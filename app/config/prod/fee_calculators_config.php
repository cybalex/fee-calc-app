<?php

use FeeCalcApp\Calculator\Fee\SimpleCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCustomCurrencyCalculator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return function(ContainerConfigurator $configurator) {
    $configurator->parameters()
        ->set('supported_operation_types', [
            'withdraw',
            'deposit'
        ])
        ->set('supported_client_types', [
            'private',
            'business',
        ])
        ->set('currency_default_code', 'EUR')
        ->set('supported_currency_codes', [
            'EUR',
            'USD',
            'JPY',
        ])
        ->set('currency_default_scale', 2)
        ->set('currency_scale_map', ['JPY' => 0])
        ->set('date_format', 'Y-m-d')
        ->set('deposit_fee_rate', 0.0003)
        ->set('private_withdrawal_free_weekly_amount', 100000)
        ->set('private_withdrawal_max_weekly_discounts_number', 3)
        ->set('withdrawal_private_fee_rate', 0.003)
        ->set('withdrawal_business_fee_rate', 0.005)
        ->set('fee_calculation_config', [
            'deposit_calculator' => [
                'calculator' => SimpleCalculator::class,
                'enabled' => true,
                'params' => [
                    'fee_rate' => param('deposit_fee_rate'),
                ],
                'requirements' => [
                    'operation_type' => 'deposit',
                ]
            ],
            'withdrawal_business_calculator' => [
                'calculator' => SimpleCalculator::class,
                'enabled' => true,
                'params' => [
                    'fee_rate' => param('withdrawal_business_fee_rate'),
                ],
                'requirements' => [
                    'client_type' => 'business',
                    'operation_type' => 'withdraw',
                ]
            ],
            'withdrawal_private_no_discount_calculator' => [
                'calculator' => SimpleCalculator::class,
                'enabled' => true,
                'params' => [
                    'fee_rate' => param('withdrawal_private_fee_rate'),
                ],
                'requirements' => [
                    'operation_type' => 'withdraw',
                    'client_type' => 'private',
                    'weekly_transactions' => ['>=', param('private_withdrawal_max_weekly_discounts_number')],
                    'currency_code' => param('currency_default_code')
                ],
            ],
            'withdrawal_private_calculator' => [
                'calculator' => WithdrawalPrivateCalculator::class,
                'enabled' => true,
                'extends' => 'withdrawal_private_no_discount_calculator',
                'params' => [
                    'free_weekly_transaction_amount' => param('private_withdrawal_free_weekly_amount'),
                ],
                'requirements' => [
                    'weekly_transactions' => ['<', param('private_withdrawal_max_weekly_discounts_number')],
                ]
            ],
            'withdrawal_private_custom_currency_calculator' => [
                'calculator' => WithdrawalPrivateCustomCurrencyCalculator::class,
                'enabled' => true,
                'extends' => 'withdrawal_private_calculator',
                'requirements' => [
                    'currency_code' => ['!=', param('currency_default_code')],
                ]
            ]
        ]);
    };
