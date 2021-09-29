<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use FeeCalcApp\Calculator\CalculatorDecorator;
use FeeCalcApp\Calculator\Config\ConfigBuilderInterface;
use FeeCalcApp\Calculator\FeeCalculatorInterface;

class FeeCalculatorCollectionFactory
{
    private ConfigBuilderInterface $configBuilder;
    private CalculatorDecorator $calculatorDecorator;
    /**
     * @var FeeCalculatorInterface[]
     */
    private array $feeCalculators = [];

    public function __construct(
        iterable $feeCalculators,
        ConfigBuilderInterface $configBuilder,
        CalculatorDecorator $calculatorDecorator
    ) {
        $this->configBuilder = $configBuilder;
        $this->calculatorDecorator = $calculatorDecorator;
        foreach ($feeCalculators as $feeCalculator) {
            $this->addFeeCalculator($feeCalculator);
        }
    }

    public function get(): array
    {
        return $this->feeCalculators;
    }

    private function addFeeCalculator(FeeCalculatorInterface $feeCalculator)
    {
        $calculatorsConfig = $this->configBuilder->getConfig();
        $feeCalculatorClass = get_class($feeCalculator);

        foreach ($calculatorsConfig as $calculatorName => $calculatorConfig) {
            if ($calculatorConfig['calculator'] === $feeCalculatorClass) {
                $feeCalculatorClone = clone $feeCalculator;
                $this->calculatorDecorator->compileFilters($calculatorName, $feeCalculatorClone);
                $this->calculatorDecorator->compileParametersConfig($calculatorName, $feeCalculatorClone);
                $this->feeCalculators[$calculatorName] = $feeCalculatorClone;
            }
        }
    }
}
