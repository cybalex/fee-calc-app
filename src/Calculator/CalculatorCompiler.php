<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator;

use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\Config\FilterProvider;
use FeeCalcApp\Calculator\Config\Params\ParamBag;
use FeeCalcApp\Calculator\Config\Params\ParametersFactory;
use RuntimeException;

class CalculatorCompiler
{
    private FilterProvider $filterProvider;
    private ConfigBuilder $configBuilder;
    private ParametersFactory $paramFactory;

    public function __construct(
        FilterProvider $filterProvider,
        ConfigBuilder $configBuilder,
        ParametersFactory $paramFactory
    ) {
        $this->filterProvider = $filterProvider;
        $this->configBuilder = $configBuilder;
        $this->paramFactory = $paramFactory;
    }

    public function compileFilters(FeeCalculatorInterface $feeCalculator): void
    {
        $feeCalculatorClass = get_class($feeCalculator);

        if (!isset($this->configBuilder->getConfig()[$feeCalculatorClass])) {
            throw new RuntimeException(sprintf('No config for "%s" fee calculator was found in the config', $feeCalculatorClass));
        }

        foreach ($this->filterProvider->get($feeCalculatorClass) as $filter) {
            $feeCalculator->addFilter($filter);
        }
    }

    public function compileParametersConfig(FeeCalculatorInterface $feeCalculator): void
    {
        $feeCalculatorClass = get_class($feeCalculator);

        if (!isset($this->configBuilder->getConfig()[$feeCalculatorClass])) {
            throw new RuntimeException(sprintf('No config for "%s" fee calculator was found in the config', $feeCalculatorClass));
        }

        $parametersArray = $this->configBuilder->getConfig()[$feeCalculatorClass]['params'] ?? [];

        $paramItems = [];

        foreach ($parametersArray as $name => $value) {
            $paramItems[] = $this->paramFactory->getParamItem($name, $value);
        }

        $parameterBag = new ParamBag($paramItems);

        $feeCalculator->setParamBag($parameterBag);
    }
}
