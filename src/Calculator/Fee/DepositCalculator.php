<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;

class DepositCalculator implements FeeCalculatorInterface
{
    private float $feeRate;
    private Math $math;

    public function __construct(Math $math, float $feeRate)
    {
        $this->math = $math;
        $this->feeRate = $feeRate;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        return $this->math->mul((string) $transactionDto->getAmount(), (string) $this->feeRate);
    }

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        return $transactionDto->getOperationType() === TransactionDto::OPERATION_TYPE_DEPOSIT;
    }
}
