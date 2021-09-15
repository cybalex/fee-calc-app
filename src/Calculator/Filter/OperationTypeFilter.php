<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

use FeeCalcApp\Calculator\DecisionMaker\DecisionMakerFactory;
use FeeCalcApp\DTO\TransactionDto;

class OperationTypeFilter extends AbstractFilter
{
    public function __construct(array $operationTypes, string $comparison, DecisionMakerFactory $decisionMakerFactory)
    {
        parent::__construct($decisionMakerFactory);
        $this->checkComparisonSupport($comparison);
        $this->valuesToCompareWith = $operationTypes;
        $this->comparison = $comparison;
    }

    public function getSupportedComparisonTypes(): array
    {
        return [
            self::COMPARISON_EQUALS,
            self::COMPARISON_NOT_EQUALS,
        ];
    }

    protected function getValueBeingCompared(TransactionDto $transactionDto)
    {
        return $transactionDto->getOperationType();
    }
}
