<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Config;

use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\Fee\DepositCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalBusinessCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateNoDiscountCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConfigBuilderTest extends TestCase
{
    public function testInvalidParentConfigReference(): void
    {
        $rowConfig = [
            WithdrawalPrivateCalculator::class => [
                'extends' => WithdrawalPrivateNoDiscountCalculator::class,
                'enabled' => true,
            ]
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No fee calculation strategy config name "FeeCalcApp\Calculator\Fee\WithdrawalPrivateNoDiscountCalculator" found');

        $configBuilder = new ConfigBuilder($rowConfig);
        $configBuilder->getConfig();
    }

    /**
     * @dataProvider configProvider
     */
    public function testMergeParentConfig(array $rawConfig): void
    {
        $configBuilder = new ConfigBuilder($rawConfig);
        $resultingConfig = $configBuilder->getConfig();

        $this->assertEquals(
            'withdraw',
            $resultingConfig[WithdrawalPrivateNoDiscountCalculator::class]['requirements']['operation_type']
        );
    }

    public function configProvider(): \Generator
    {
        $config = [
            DepositCalculator::class => [
                'enabled' => true,
                'requirements' => [
                    'operation_type' => 'deposit',
                ]
            ],
            WithdrawalBusinessCalculator::class => [
                'enabled' => true,
                'extends' => DepositCalculator::class,
                'requirements' => [
                    'operation_type' => 'withdraw',
                ]
            ],
            WithdrawalPrivateNoDiscountCalculator::class => [
                'enabled' => true,
                'extends' => WithdrawalBusinessCalculator::class,
            ],

        ];

        yield([$config]);
        yield([array_reverse($config)]);
    }
}
