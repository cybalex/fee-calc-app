<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeDiscountCalculatorInterface;
use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateCalculator extends WithdrawalPrivateNoDiscountCalculator implements FeeDiscountCalculatorInterface
{
    protected ?array $transactionsWithinAWeek = null;

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

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        if (
            !($transactionDto->getClientType() === TransactionDto::CLIENT_TYPE_PRIVATE
            && $transactionDto->getOperationType() === TransactionDto::OPERATION_TYPE_WITHDRAW
            && $transactionDto->getCurrencyCode() === $this->currencyConfig->getDefaultCurrencyCode())
        ) {
            return false;
        }

        $this->transactionsWithinAWeek = $this->transactionHistoryManager->getUserTransactionsWithinAWeek($transactionDto);

        return count($this->transactionsWithinAWeek) < $this->maxWeeklyDiscountsNumber;
    }

    public function calculateDiscount(TransactionDto $transactionDto, string $maxFeeInCurrency): string
    {
        $totalAmountWithdrawalsForAWeek = $this->transactionHistoryManager
            ->getUserTransactionsTotalAmount($this->transactionsWithinAWeek, $this->currencyConfig->getDefaultCurrencyCode());

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
