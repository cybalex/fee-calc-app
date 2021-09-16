<?php

declare(strict_types=1);

namespace FeeCalcApp\Functional\Calculator\Config;

use AppFactory;
use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\Config\ConfigBuilderInterface;
use FeeCalcApp\Calculator\Config\FilterProvider;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class FilterProviderTest extends TestCase
{
    public function testGetCalculatorNotFound(): void
    {
        $rowConfig = [
            WithdrawalPrivateCalculator::class => [
                'enabled' => true,
            ]
        ];

        $configBuilder = new ConfigBuilder($rowConfig);

        $appFactory = new AppFactory();
        $app = $appFactory->create('test');
        $container = $app->buildContainer([ConfigBuilderInterface::class => $configBuilder]);

        $filterProvider = $container->get(FilterProvider::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fee calculator config was not found for stdClass');
        $filterProvider->get(stdClass::class);
    }
}
