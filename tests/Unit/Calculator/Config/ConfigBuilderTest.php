<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Config;

use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\Fee\SimpleCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ConfigBuilderTest extends TestCase
{
    public function testInvalidParentConfigReference(): void
    {
        $rowConfig = [
            WithdrawalPrivateCalculator::class => [
                'extends' => SimpleCalculator::class,
                'enabled' => true,
            ]
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No fee calculation strategy config name "FeeCalcApp\Calculator\Fee\SimpleCalculator" found');

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
            $resultingConfig['withdrawal_private_no_discount']['requirements']['operation_type']
        );
    }

    public function configProvider(): \Generator
    {
        $config = [
            'deposit_calculator' => [
                'calculator' => SimpleCalculator::class,
                'enabled' => true,
                'requirements' => [
                    'operation_type' => 'deposit',
                ]
            ],
            'withdrawal_calculator' => [
                'calculator' => SimpleCalculator::class,
                'enabled' => true,
                'extends' => 'deposit_calculator',
                'requirements' => [
                    'operation_type' => 'withdraw',
                ]
            ],
            'withdrawal_private_no_discount' => [
                'enabled' => true,
                'extends' => 'withdrawal_calculator',
            ],
        ];

        yield([$config]);
        yield([array_reverse($config)]);
    }
}
