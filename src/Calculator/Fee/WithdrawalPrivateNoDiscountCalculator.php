<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\CalculatorCompiler;
use FeeCalcApp\Calculator\Config\Params\Exception\MissingConfigParameterException;
use FeeCalcApp\Calculator\Config\Params\Item\FeeRateParameter;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateNoDiscountCalculator extends AbstractCalculator
{
    protected Math $math;
    protected TransactionHistoryManager $transactionHistoryManager;

    public function __construct(
        CalculatorCompiler $calculatorCompiler,
        Math $math,
        TransactionHistoryManager $transactionHistoryManager
    ) {
        parent::__construct($calculatorCompiler);
        $this->math = $math;
        $this->transactionHistoryManager = $transactionHistoryManager;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        return $this->math->mul(
            (string)$transactionDto->getAmount(),
            (string)$this->paramBag->getParam(FeeRateParameter::PARAM_NAME)->getValue()
        );
    }
}
