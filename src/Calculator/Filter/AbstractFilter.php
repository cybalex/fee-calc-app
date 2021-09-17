<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

use FeeCalcApp\Calculator\DecisionMaker\DecisionMakerFactory;
use FeeCalcApp\DTO\TransactionDto;
use InvalidArgumentException;

abstract class AbstractFilter implements FilterInterface, ComparisonInterface
{
    protected array $valuesToCompareWith;

    protected string $comparison;

    private DecisionMakerFactory $decisionMakerFactory;

    public function __construct(DecisionMakerFactory $decisionMakerFactory)
    {
        $this->decisionMakerFactory = $decisionMakerFactory;
    }

    public function evaluate($value1, $value2, string $comparison): bool
    {
        switch ($comparison) {
            case self::COMPARISON_EQUALS:
                return $value1 === $value2;
            case self::COMPARISON_NOT_EQUALS:
                return $value1 !== $value2;
            case self::COMPARISON_GREATER_OR_EQUALS:
                return $value1 >= $value2;
            case self::COMPARISON_GREATER:
                return $value1 > $value2;
            case self::COMPARISON_LESS_THEN:
                return $value1 < $value2;
            case self::COMPARISON_LESS_THEN_OR_EQUALS:
                return $value1 <= $value2;
        }

        throw new InvalidArgumentException(sprintf('Filter comparison %s is not supported', $comparison));
    }

    /**
     * @return mixed
     */
    abstract protected function getValueBeingCompared(TransactionDto $transactionDto);

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        $callback = [$this, 'evaluate'];

        $argumentsCollection = array_map(
            function ($valueToCompareWith) use ($transactionDto) {
                return [$this->getValueBeingCompared($transactionDto), $valueToCompareWith, $this->comparison];
            },
            $this->valuesToCompareWith
        );

        return $this->decisionMakerFactory->get($this->comparison)->decide($callback, $argumentsCollection);
    }

    protected function checkComparisonSupport($comparison): void
    {
        if (!in_array($comparison, $this->getSupportedComparisonTypes(), true)) {
            throw new InvalidArgumentException(sprintf('Provided comparison operation "%s" is not supported by %s', $comparison, __CLASS__));
        }
    }
}
