<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;

class WithdrawalBusinessCalculator implements FeeCalculatorInterface
{
    private Math $math;
    private float $withdrawFeeRate; //0.005

    public function __construct(Math $math, float $withdrawFeeRate)
    {
        $this->math = $math;
        $this->withdrawFeeRate = $withdrawFeeRate;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        return $this->math->mul((string) $transactionDto->getAmount(), (string) $this->withdrawFeeRate);
    }

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        return $transactionDto->getClientType() === TransactionDto::CLIENT_TYPE_BUSINESS
            && $transactionDto->getOperationType() === TransactionDto::OPERATION_TYPE_WITHDRAW;
    }
}
