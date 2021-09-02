<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeDiscountCalculatorInterface;
use FeeCalcApp\DTO\Currency;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateCacheProxy;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateCalculator extends WithdrawalPrivateNoDiscountCalculator implements FeeDiscountCalculatorInterface
{
    private ?array $transactionsWithinAWeek = null;
    private ExchangeRateCacheProxy $exchangeRateCacheProxy;
    private string $defaultCurrencyCode;
    private int $freeWithdrawalWeeklyAmount;
    private int $maxWeeklyDiscountsNumber;

    public function __construct(
        TransactionHistoryManager $transactionHistoryManager,
        ExchangeRateCacheProxy $cacheProxy,
        Math $math,
        float $withdrawalFeeRate,
        string $defaultCurrencyCode,
        int $freeWithdrawalWeeklyAmount,
        int $maxWeeklyDiscountsNumber
    ) {
        parent::__construct($math, $transactionHistoryManager, $withdrawalFeeRate);

        $this->exchangeRateCacheProxy = $cacheProxy;
        $this->defaultCurrencyCode = $defaultCurrencyCode;
        $this->freeWithdrawalWeeklyAmount = $freeWithdrawalWeeklyAmount;
        $this->maxWeeklyDiscountsNumber = $maxWeeklyDiscountsNumber;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        $maxFee = parent::calculate($transactionDto);

        return $this->math->sub($maxFee, $this->calculateDiscount($transactionDto, $maxFee));
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

        return count($this->transactionsWithinAWeek) < $this->maxWeeklyDiscountsNumber;
    }

    public function calculateDiscount(TransactionDto $transactionDto, string $feeInCurrency): string
    {
        $totalAmountWithdrawalsForAWeek = $this->transactionHistoryManager
            ->getUserTransactionsTotalAmount($this->transactionsWithinAWeek, $this->defaultCurrencyCode);

        $discountInEuroCent = $this->math->mul(
            (string) $this->withdrawalFeeRate,
            $this->math->max(
                $this->math->sub((string) $this->freeWithdrawalWeeklyAmount, $totalAmountWithdrawalsForAWeek),
                '0'
            )
        );

        $transactionCurrencyCode = $transactionDto->getCurrency()->getCode();

        if ($transactionCurrencyCode === $this->defaultCurrencyCode) {
            return $this->math->floor($this->math->min($discountInEuroCent, $feeInCurrency));
        }

        $maxDiscountInTransactionCurrency =
            $this->math->div(
                $this->math->mul(
                    $discountInEuroCent,
                    (string) $this->exchangeRateCacheProxy->getExchangeRateForDate(
                        $transactionDto->getDate(),
                        $this->defaultCurrencyCode,
                        $transactionCurrencyCode
                    )),
                (string) pow(10, Currency::DEFAULT_SCALE - $transactionDto->getCurrency()->getScale())
            )
        ;

        return $this->math->floor($this->math->min($maxDiscountInTransactionCurrency, $feeInCurrency));
    }
}
