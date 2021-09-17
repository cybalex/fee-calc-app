<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator;

use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\Config\FilterProvider;
use RuntimeException;

class CalculatorCompiler
{
    private FilterProvider $filterProvider;
    private ConfigBuilder $configBuilder;

    public function __construct(FilterProvider $filterProvider, ConfigBuilder $configBuilder)
    {
        $this->filterProvider = $filterProvider;
        $this->configBuilder = $configBuilder;
    }

    public function compileFilters(
        FeeCalculatorInterface $feeCalculator
    ): FeeCalculatorInterface {
        $feeCalculatorClass = get_class($feeCalculator);

        if (!isset($this->configBuilder->getConfig()[$feeCalculatorClass])) {
            throw new RuntimeException(sprintf('No config for "%s" fee calculator was found in the config', $feeCalculatorClass));
        }

        foreach ($this->filterProvider->get($feeCalculatorClass) as $filter) {
            $feeCalculator->addFilter($filter);
        }

        return $feeCalculator;
    }
}
