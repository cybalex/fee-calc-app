<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\Dto\TransactionDto;

class WithdrawalBusinessCalculator implements FeeCalculatorInterface
{
    public function calculate(TransactionDto $transaction): int
    {
        return (int) ceil($transaction->getAmount() * 0.005);
    }

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        return $transactionDto->getClientType() === TransactionDto::CLIENT_TYPE_BUSINESS
            && $transactionDto->getOperationType() === TransactionDto::OPERATION_TYPE_WITHDRAW;
    }
}
