<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

use FeeCalcApp\Calculator\DecisionMaker\DecisionMakerFactory;
use FeeCalcApp\DTO\TransactionDto;

class CurrencyFilter extends AbstractFilter
{
    public function __construct(array $currencyCodes, string $comparison, DecisionMakerFactory $decisionMakerFactory)
    {
        parent::__construct($decisionMakerFactory);
        $this->checkComparisonSupport($comparison);
        $this->valuesToCompareWith = $currencyCodes;
        $this->comparison = $comparison;
    }

    protected function getValueBeingCompared(TransactionDto $transactionDto)
    {
        return $transactionDto->getCurrencyCode();
    }

    public function getSupportedComparisonTypes(): array
    {
        return [
            self::COMPARISON_EQUALS,
            self::COMPARISON_NOT_EQUALS,
        ];
    }
}
