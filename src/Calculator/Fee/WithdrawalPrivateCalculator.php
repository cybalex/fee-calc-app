<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeDiscountCalculatorInterface;
use FeeCalcApp\DTO\Currency;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateCacheProxy;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateCalculator extends WithdrawalPrivateNoDiscountCalculator implements FeeDiscountCalculatorInterface
{
    private const CALCULATION_CURRENCY_CODE = Currency::EUR_CODE;
    private const FREE_WITHDRAWAL_WEEKLY_AMOUNT_PENNY = 100000; // 10 000 euro cent

    private ?array $transactionsWithinAWeek = null;

    private ExchangeRateCacheProxy $exchangeRateCacheProxy;

    public function __construct(TransactionHistoryManager $transactionHistoryManager, ExchangeRateCacheProxy $cacheProxy)
    {
        parent::__construct($transactionHistoryManager);

        $this->exchangeRateCacheProxy = $cacheProxy;
    }

    public function calculate(TransactionDto $transaction): int
    {
        $maxFee = parent::calculate($transaction);

        return $maxFee - $this->calculateDiscount($transaction, $maxFee);
    }

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        if (
            !($transactionDto->getClientType() === TransactionDto::CLIENT_TYPE_PRIVATE
            && $transactionDto->getOperationType() === TransactionDto::OPERATION_TYPE_WITHDRAW)
        ) {
            return false;
        }

        $this->transactionsWithinAWeek = $this->transactionHistoryManager->getUserTransactionsWithinAWeek($transactionDto);

        return count($this->transactionsWithinAWeek) < 3;
    }

    public function calculateDiscount(TransactionDto $transactionDto, int $feeInCurrency): int
    {
        $totalAmountWithdrawalsForAWeek = $this->transactionHistoryManager
            ->getUserTransactionsTotalAmount($this->transactionsWithinAWeek, self::CALCULATION_CURRENCY_CODE);

        $discountInEuroCent = floor(WithdrawalPrivateNoDiscountCalculator::WITHDRAWAL_FEE * max(
                self::FREE_WITHDRAWAL_WEEKLY_AMOUNT_PENNY - $totalAmountWithdrawalsForAWeek,
                0
            ))
        ;

        $transactionCurrencyCode = $transactionDto->getCurrency()->getCode();

        if ($transactionCurrencyCode === self::CALCULATION_CURRENCY_CODE) {
            return min((int) $discountInEuroCent, $feeInCurrency);
        }

        $maxDiscountInTransactionCurrency = $discountInEuroCent * $this->exchangeRateCacheProxy->getExchangeRateForDate(
                $transactionDto->getDate(),
                self::CALCULATION_CURRENCY_CODE,
                $transactionCurrencyCode
            ) / pow(10, Currency::DEFAULT_SCALE - $transactionDto->getCurrency()->getScale())
        ;

        return min((int) $maxDiscountInTransactionCurrency, $feeInCurrency);
    }
}
