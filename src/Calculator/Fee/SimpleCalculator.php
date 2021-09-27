<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\Config\Params\Item\FeeRateParameter;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\Math;

class SimpleCalculator extends AbstractCalculator
{
    protected Math $math;

    public function __construct(Math $math) {
        $this->math = $math;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        return $this->math->mul(
            (string)$transactionDto->getAmount(),
            (string)$this->paramBag->getParam(FeeRateParameter::PARAM_NAME)->getValue()
        );
    }
}
