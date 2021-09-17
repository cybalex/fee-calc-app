<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;

class WithdrawalBusinessCalculator extends AbstractCalculator
{
    private Math $math;
    private float $withdrawFeeRate;

    public function __construct(Math $math, float $withdrawFeeRate)
    {
        $this->math = $math;
        $this->withdrawFeeRate = $withdrawFeeRate;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        return $this->math->mul((string) $transactionDto->getAmount(), (string) $this->withdrawFeeRate);
    }
}
