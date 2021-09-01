<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateNoDiscountCalculator implements FeeCalculatorInterface
{
    protected const WITHDRAWAL_FEE = 0.003;

    protected TransactionHistoryManager $transactionHistoryManager;

    public function __construct(TransactionHistoryManager $transactionHistoryManager)
    {
        $this->transactionHistoryManager = $transactionHistoryManager;
    }

    public function calculate(TransactionDto $transaction): int
    {
        return (int) ceil(($transaction->getAmount()) * self::WITHDRAWAL_FEE);
    }

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        return $transactionDto->getClientType() === TransactionDto::CLIENT_TYPE_PRIVATE
            && $transactionDto->getOperationType() === TransactionDto::OPERATION_TYPE_WITHDRAW
            && count($this->transactionHistoryManager->getUserTransactionsWithinAWeek($transactionDto)) > 2;
    }
}
