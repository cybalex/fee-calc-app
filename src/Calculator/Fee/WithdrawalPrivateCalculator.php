<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeDiscountCalculatorInterface;
use FeeCalcApp\DTO\Currency;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateCalculator extends WithdrawalPrivateNoDiscountCalculator implements FeeDiscountCalculatorInterface
{
    private ?array $transactionsWithinAWeek = null;

    private ExchangeRateClientInterface $exchangeRateClient;

    private string $defaultCurrencyCode;

    private int $freeWithdrawalWeeklyAmount;

    public function __construct(
        Math $math,
        TransactionHistoryManager $transactionHistoryManager,
        float $withdrawalFeeRate,
        int $maxWeeklyDiscountsNumber,
        ExchangeRateClientInterface $exchangeRateClient,
        string $defaultCurrencyCode,
        int $freeWithdrawalWeeklyAmount
    ) {
        parent::__construct($math, $transactionHistoryManager, $withdrawalFeeRate, $maxWeeklyDiscountsNumber);

        $this->exchangeRateClient = $exchangeRateClient;
        $this->defaultCurrencyCode = $defaultCurrencyCode;
        $this->freeWithdrawalWeeklyAmount = $freeWithdrawalWeeklyAmount;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        $maxFeeInCurrency = parent::calculate($transactionDto);

        return $this->math->sub($maxFeeInCurrency, $this->calculateDiscount($transactionDto, $maxFeeInCurrency));
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

    public function calculateDiscount(TransactionDto $transactionDto, string $maxFeeInCurrency): string
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
            return $this->math->floor($this->math->min($discountInEuroCent, $maxFeeInCurrency));
        }

        $maxDiscountInTransactionCurrency =
            $this->math->div(
                $this->math->mul(
                    $discountInEuroCent,
                    (string) $this->exchangeRateClient->getExchangeRateForDate(
                        $transactionDto->getDate(),
                        $this->defaultCurrencyCode,
                        $transactionCurrencyCode
                    )),
                (string) pow(10, Currency::DEFAULT_SCALE - $transactionDto->getCurrency()->getScale())
            )
        ;

        return $this->math->floor($this->math->min($maxDiscountInTransactionCurrency, $maxFeeInCurrency));
    }
}
