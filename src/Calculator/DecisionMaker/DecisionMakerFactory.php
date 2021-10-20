<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\DecisionMaker;

use FeeCalcApp\Calculator\Filter\ComparisonInterface;
use JetBrains\PhpStorm\Pure;

class DecisionMakerFactory
{
    #[Pure]
    public function get(string $comparisonType): DecisionMakerInterface
    {
        return match ($comparisonType) {
            ComparisonInterface::COMPARISON_EQUALS => new AtLeastOneTrueDecisionMaker(),
            default => new AllTrueDecisionMaker(),
        };
    }
}
