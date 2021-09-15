<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeDiscountCalculatorInterface;
use FeeCalcApp\Calculator\Filter\FilterInterface;
use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateCalculator extends WithdrawalPrivateNoDiscountCalculator implements FeeDiscountCalculatorInterface, FilterInterface
{
    protected CurrencyConfig $currencyConfig;

    protected int $freeWithdrawalWeeklyAmount;

    public function __construct(
        Math $math,
        TransactionHistoryManager $transactionHistoryManager,
        float $withdrawalFeeRate,
        int $maxWeeklyDiscountsNumber,
        CurrencyConfig $currencyConfig,
        int $freeWithdrawalWeeklyAmount
    ) {
        parent::__construct($math, $transactionHistoryManager, $withdrawalFeeRate, $maxWeeklyDiscountsNumber);

        $this->currencyConfig = $currencyConfig;
        $this->freeWithdrawalWeeklyAmount = $freeWithdrawalWeeklyAmount;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        $maxFeeInCurrency = parent::calculate($transactionDto);

        return $this->math->sub($maxFeeInCurrency, $this->calculateDiscount($transactionDto, $maxFeeInCurrency));
    }

    public function calculateDiscount(TransactionDto $transactionDto, string $maxFeeInCurrency): string
    {
        $transactionsWithinAWeek = $this->transactionHistoryManager->getUserTransactionsWithinAWeek($transactionDto);

        $totalAmountWithdrawalsForAWeek = $this->transactionHistoryManager
            ->getUserTransactionsTotalAmount($transactionsWithinAWeek, $this->currencyConfig->getDefaultCurrencyCode());

        $maxDiscountInTransactionCurrency = $this->getDiscountInTransactionCurrency($transactionDto, $totalAmountWithdrawalsForAWeek);

        return $this->math->floor($this->math->min($maxDiscountInTransactionCurrency, $maxFeeInCurrency));
    }

    protected function getDiscountInTransactionCurrency(
        TransactionDto $transactionDto,
        string $totalAmountWithdrawalsForAWeek
    ): string {
        return $this->math->mul(
            (string) $this->withdrawalFeeRate,
            $this->math->max(
                $this->math->sub((string) $this->freeWithdrawalWeeklyAmount, $totalAmountWithdrawalsForAWeek),
                '0'
            )
        );
    }
}
