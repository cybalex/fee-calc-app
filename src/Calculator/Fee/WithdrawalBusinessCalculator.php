<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\CalculatorCompiler;
use FeeCalcApp\Calculator\Config\Params\Item\FeeRateParameter;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;

class WithdrawalBusinessCalculator extends AbstractCalculator
{
    private Math $math;

    public function __construct(CalculatorCompiler $calculatorCompiler, Math $math)
    {
        parent::__construct($calculatorCompiler);
        $this->math = $math;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        return $this->math->mul(
            (string) $transactionDto->getAmount(),
            (string) $this->paramBag->getParam(FeeRateParameter::PARAM_NAME)->getValue()
        );
    }
}
