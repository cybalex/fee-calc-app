<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateNoDiscountCalculator extends AbstractCalculator
{
    protected Math $math;
    protected TransactionHistoryManager $transactionHistoryManager;
    protected float $withdrawalFeeRate;
    protected int $maxWeeklyDiscountsNumber;

    public function __construct(
        Math $math,
        TransactionHistoryManager $transactionHistoryManager,
        float $withdrawalFeeRate,
        int $maxWeeklyDiscountsNumber
    ) {
        $this->math = $math;
        $this->transactionHistoryManager = $transactionHistoryManager;
        $this->withdrawalFeeRate = $withdrawalFeeRate;
        $this->maxWeeklyDiscountsNumber = $maxWeeklyDiscountsNumber;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        return $this->math->mul((string) $transactionDto->getAmount(), (string) $this->withdrawalFeeRate);
    }
}
