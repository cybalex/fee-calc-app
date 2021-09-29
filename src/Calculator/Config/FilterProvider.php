<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config;

use FeeCalcApp\Calculator\Filter\FilterCreator;
use FeeCalcApp\Calculator\Filter\FilterInterface;
use InvalidArgumentException;

class FilterProvider
{
    private FilterCreator $filterCreator;

    public function __construct(FilterCreator $filterCreator)
    {
        $this->filterCreator = $filterCreator;
    }

    protected function getFilterConfig(string $calculatorName, array $filtersConfig): array
    {
        if (!isset($filtersConfig[$calculatorName])) {
            throw new InvalidArgumentException(sprintf('Fee calculator config was not found for %s', $calculatorName));
        }

        $calculatorConfig = $filtersConfig[$calculatorName];

        return array_merge(
            ['is_enabled' => $calculatorConfig['enabled']],
            $calculatorConfig['requirements']
        );
    }

    /**
     * @return FilterInterface[]
     */
    public function get(string $calculatorName, array $filtersConfig): array
    {
        $filters = [];

        foreach ($this->getFilterConfig($calculatorName, $filtersConfig) as $filterName => $filterConfig) {
            if (is_scalar($filterConfig)) {
                $filterConfig = [$filterConfig];
            }

            $filters[] = $this->filterCreator->getFilterInstance($filterName, $filterConfig);
        }

        return $filters;
    }
}
