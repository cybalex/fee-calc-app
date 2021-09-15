<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\DecisionMaker;

use FeeCalcApp\Calculator\Filter\ComparisonInterface;

class DecisionMakerFactory
{
    public function get(string $comparisonType): DecisionMakerInterface
    {
        switch ($comparisonType) {
            case ComparisonInterface::COMPARISON_EQUALS:
                return new AtLeastOneTrueDecisionMaker();
            default:
                return new AllTrueDecisionMaker();
        }
    }
}
