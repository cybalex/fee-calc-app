<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\DecisionMaker;

interface DecisionMakerInterface
{
    public function decide(callable $callback, array $argumentsCollection): bool;
}
