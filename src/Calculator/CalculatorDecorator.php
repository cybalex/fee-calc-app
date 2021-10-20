<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator;

use FeeCalcApp\Calculator\Config\ConfigBuilderInterface;
use FeeCalcApp\Calculator\Config\FilterProvider;
use FeeCalcApp\Calculator\Config\Params\ParamBag;
use FeeCalcApp\Calculator\Config\Params\ParametersFactory;
use RuntimeException;

class CalculatorDecorator
{
    public function __construct(
        private FilterProvider $filterProvider,
        private ConfigBuilderInterface $configBuilder,
        private ParametersFactory $paramFactory
    ) {
    }

    public function compileFilters(string $calculatorName, FeeCalculatorInterface $feeCalculator): void
    {
        $feeCalculatorClass = get_class($feeCalculator);

        $calculatorsConfig = $this->configBuilder->getConfig();

        if (!$this->calculatorConfigExists($calculatorsConfig, $feeCalculatorClass)) {
            throw new RuntimeException(sprintf('No config for "%s" fee calculator was found in the config', $feeCalculatorClass));
        }

        foreach ($this->filterProvider->get($calculatorName, $calculatorsConfig) as $filter) {
            $feeCalculator->addFilter($filter);
        }
    }

    public function compileParametersConfig(string $calculatorName, FeeCalculatorInterface $feeCalculator): void
    {
        $config = $this->configBuilder->getConfig();
        $feeCalculatorClass = get_class($feeCalculator);

        if (!isset($config[$calculatorName])) {
            throw new RuntimeException(sprintf('No config for "%s" fee calculator was found in the config', $calculatorName));
        }

        if (!$this->calculatorConfigExists($config, $feeCalculatorClass)) {
            throw new RuntimeException(sprintf('No config for "%s" fee calculator was found in the config', $feeCalculatorClass));
        }

        $parametersArray = $this->configBuilder->getConfig()[$calculatorName]['params'] ?? [];

        $paramItems = [];

        foreach ($parametersArray as $name => $value) {
            $paramItems[] = $this->paramFactory->getParamItem($name, $value);
        }

        $parameterBag = new ParamBag($paramItems);

        $feeCalculator->setParamBag($parameterBag);
    }

    private function calculatorConfigExists(array $calculatorsConfig, string $calculatorClass): bool
    {
        foreach ($calculatorsConfig as $calculatorConfig) {
            if ($calculatorConfig['calculator'] === $calculatorClass) {
                return true;
            }
        }

        return false;
    }
}
