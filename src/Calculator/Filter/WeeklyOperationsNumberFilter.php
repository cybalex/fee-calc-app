<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

use FeeCalcApp\Calculator\DecisionMaker\DecisionMakerFactory;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\TransactionHistoryManager;

class WeeklyOperationsNumberFilter extends AbstractFilter
{
    public function __construct(
        private TransactionHistoryManager $transactionHistoryManager,
        int $value,
        string $comparison,
        DecisionMakerFactory $decisionMakerFactory
    ) {
        parent::__construct($decisionMakerFactory);
        $this->checkComparisonSupport($comparison);
        $this->valuesToCompareWith = [$value];
        $this->comparison = $comparison;
    }

    protected function getValueBeingCompared(TransactionDto $transactionDto)
    {
        $this->transactionHistoryManager->getUserTransactionsWithinAWeek($transactionDto);
    }

    public function getSupportedComparisonTypes(): array
    {
        return [
                self::COMPARISON_NOT_EQUALS,
                self::COMPARISON_EQUALS,
                self::COMPARISON_GREATER_OR_EQUALS,
                self::COMPARISON_GREATER,
                self::COMPARISON_LESS_THEN,
                self::COMPARISON_LESS_THEN_OR_EQUALS,
        ];
    }
}
