<?php

use DI\Container;
use FeeCalcApp\Calculator\Fee\SimpleCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCustomCurrencyCalculator;

return [
    'fee_calculation_config' => [
        'deposit_calculator' => [
            'calculator' => SimpleCalculator::class,
            'enabled' => true,
            'params' => [
                'fee_rate' => function(Container $c) {
                    return $c->get('deposit_fee_rate');
                },
            ],
            'requirements' => [
                'operation_type' => 'deposit',
            ]
        ],
        'withdrawal_business_calculator' => [
            'calculator' => SimpleCalculator::class,
            'enabled' => true,
            'params' => [
                'fee_rate' => function(Container $c) {
                    return $c->get('withdrawal_business_fee_rate');
                },
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
                'fee_rate' => function(Container $c) {
                    return $c->get('withdrawal_private_fee_rate');
                },
            ],
            'requirements' => [
                'operation_type' => 'withdraw',
                'client_type' => 'private',
                'weekly_transactions' => ['>=', function (Container $c) {
                    return $c->get('private_withdrawal_max_weekly_discounts_number');
                }],
                'currency_code' => function (Container $c) {
                    return $c->get('currency_default_code');
                }
            ],
        ],
        'withdrawal_private_calculator' => [
            'calculator' => WithdrawalPrivateCalculator::class,
            'enabled' => true,
            'extends' => 'withdrawal_private_no_discount_calculator',
            'params' => [
                'free_weekly_transaction_amount' => function (Container $c) {
                    return $c->get('private_withdrawal_free_weekly_amount');
                }
            ],
            'requirements' => [
                'weekly_transactions' => ['<', function (Container $c) {
                    return $c->get('private_withdrawal_max_weekly_discounts_number');
                }],
            ]
        ],
        'withdrawal_private_custom_currency_calculator' => [
            'calculator' => WithdrawalPrivateCustomCurrencyCalculator::class,
            'enabled' => true,
            'extends' => 'withdrawal_private_calculator',
            'requirements' => [
                'currency_code' => ['!=', function (Container $c) {
                    return $c->get('currency_default_code');
                }]
            ]
        ]
    ]
];
