<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

interface ComparisonInterface
{
    public const COMPARISON_NOT_EQUALS = '!=';
    public const COMPARISON_EQUALS = '==';
    public const COMPARISON_GREATER_OR_EQUALS = '>=';
    public const COMPARISON_GREATER = '>';
    public const COMPARISON_LESS_THEN = '<';
    public const COMPARISON_LESS_THEN_OR_EQUALS = '<=';

    public function getSupportedComparisonTypes(): array;

    public function evaluate($value1, $value2, string $comparison): bool;
}
