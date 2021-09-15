<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\Calculator\Filter\FilterInterface;
use FeeCalcApp\DTO\TransactionDto;

abstract class AbstractCalculator implements FeeCalculatorInterface
{
    /**
     * @var FilterInterface[]
     */
    protected array $filters = [];

    public function addFilter(FilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->isApplicable($transactionDto)) {
                return false;
            }
        }

        return true;
    }
}
