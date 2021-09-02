<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateNoDiscountCalculator implements FeeCalculatorInterface
{
    protected const WITHDRAWAL_FEE = 0.003;

    protected Math $math;
    protected TransactionHistoryManager $transactionHistoryManager;
    protected float $withdrawalFeeRate;

    public function __construct(Math $math, TransactionHistoryManager $transactionHistoryManager, float $withdrawalFeeRate)
    {
        $this->math = $math;
        $this->transactionHistoryManager = $transactionHistoryManager;
        $this->withdrawalFeeRate = $withdrawalFeeRate;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        return $this->math->mul((string) $transactionDto->getAmount(), (string) $this->withdrawalFeeRate);
    }

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        return $transactionDto->getClientType() === TransactionDto::CLIENT_TYPE_PRIVATE
            && $transactionDto->getOperationType() === TransactionDto::OPERATION_TYPE_WITHDRAW
            && count($this->transactionHistoryManager->getUserTransactionsWithinAWeek($transactionDto)) > 2;
    }
}
