<?php

declare(strict_types=1);

namespace FeeCalcApp\Functional\Calculator\Config;

use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\Config\ConfigBuilderInterface;
use FeeCalcApp\Calculator\Config\FilterProvider;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Traits\ContainerAware;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class FilterProviderTest extends TestCase
{
    use ContainerAware;

    public function testGetCalculatorNotFound(): void
    {
        $rowConfig = [
            WithdrawalPrivateCalculator::class => [
                'enabled' => true,
            ]
        ];

        $configBuilder = new ConfigBuilder($rowConfig);

        $container = $this
            ->replaceService(ConfigBuilderInterface::class, $configBuilder)
            ->getContainer();

        $filterProvider = $container->get(FilterProvider::class);
        $configBuilder = $container->get(ConfigBuilderInterface::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fee calculator config was not found for stdClass');
        $filterProvider->get(stdClass::class, $configBuilder->getConfig());
    }
}
