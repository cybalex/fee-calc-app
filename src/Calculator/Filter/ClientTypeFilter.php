<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

use FeeCalcApp\Calculator\DecisionMaker\DecisionMakerFactory;
use FeeCalcApp\DTO\TransactionDto;

class ClientTypeFilter extends AbstractFilter
{
    public function __construct(array $clientTypes, string $comparison, DecisionMakerFactory $decisionMakerFactory)
    {
        parent::__construct($decisionMakerFactory);
        $this->checkComparisonSupport($comparison);
        $this->valuesToCompareWith = $clientTypes;
        $this->comparison = $comparison;
    }

    protected function getValueBeingCompared(TransactionDto $transactionDto)
    {
        return $transactionDto->getClientType();
    }

    public function getSupportedComparisonTypes(): array
    {
        return [
            self::COMPARISON_NOT_EQUALS,
            self::COMPARISON_EQUALS,
        ];
    }
}
