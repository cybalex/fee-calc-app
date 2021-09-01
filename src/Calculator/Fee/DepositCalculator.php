<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\Dto\TransactionDto;

class DepositCalculator implements FeeCalculatorInterface
{
    private const FEE_RATE = 0.0003;

    public function calculate(TransactionDto $transaction): int
    {
        return (int) ceil($transaction->getAmount() * self::FEE_RATE);
    }

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        return $transactionDto->getOperationType() === TransactionDto::OPERATION_TYPE_DEPOSIT;
    }
}
