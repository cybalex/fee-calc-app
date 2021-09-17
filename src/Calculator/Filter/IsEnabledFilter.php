<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

use FeeCalcApp\Calculator\DecisionMaker\DecisionMakerFactory;
use FeeCalcApp\DTO\TransactionDto;

class IsEnabledFilter extends AbstractFilter
{
    private bool $isEnabled;

    public function __construct(bool $isEnabled, DecisionMakerFactory $decisionMakerFactory)
    {
        parent::__construct($decisionMakerFactory);
        $this->isEnabled = $isEnabled;
        $this->comparison = self::COMPARISON_EQUALS;
        $this->valuesToCompareWith = [true];
    }

    protected function getValueBeingCompared(TransactionDto $transactionDto)
    {
        return $this->isEnabled;
    }

    public function getSupportedComparisonTypes(): array
    {
        return [
            self::COMPARISON_EQUALS,
        ];
    }
}
