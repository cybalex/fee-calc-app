<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use DI\Container;
use FeeCalcApp\Calculator\CalculatorDecorator;
use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\FeeCalculatorInterface;

class FeeCalculatorCollectionFactory
{
    private ConfigBuilder $configBuilder;

    private Container $container;

    private CalculatorDecorator $calculatorDecorator;

    // injecting container is a bad practice.
    // though there is no way to tag services in php-di for now just to inject the subset of the needed services
    public function __construct(ConfigBuilder $configBuilder, Container $container, CalculatorDecorator $calculatorCompiler)
    {
        $this->configBuilder = $configBuilder;
        $this->container = $container;
        $this->calculatorDecorator = $calculatorCompiler;
        $this->setupCalculatorsCollection();
    }

    /**
     * @var FeeCalculatorInterface[]
     */
    private array $feeCalculators = [];

    public function get(): array
    {
        return $this->feeCalculators;
    }

    private function setupCalculatorsCollection(): void
    {
        $calculatorsConfig = $this->configBuilder->getConfig();

        foreach ($calculatorsConfig as $calculatorName => $calculatorConfig) {
            $calculator = clone $this->container->get($calculatorConfig['calculator']);
            $this->calculatorDecorator->compileFilters($calculatorName, $calculator);
            $this->calculatorDecorator->compileParametersConfig($calculatorName, $calculator);
            $this->feeCalculators[$calculatorName] = $calculator;
        }
    }
}
