<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\DecisionMaker;

class AtLeastOneTrueDecisionMaker implements DecisionMakerInterface
{
    public function decide(callable $callback, array $argumentsCollection): bool
    {
        foreach ($argumentsCollection as $arguments) {
            if ($callback(...$arguments)) {
                return true;
            }
        }

        return false;
    }
}
