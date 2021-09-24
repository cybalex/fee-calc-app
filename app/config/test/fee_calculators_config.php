<?php

use DI\Container;
use FeeCalcApp\Calculator\Fee\DepositCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalBusinessCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCustomCurrencyCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateNoDiscountCalculator;

return [
    'fee_calculation_config' => [
        DepositCalculator::class => [
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
        WithdrawalBusinessCalculator::class => [
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
        WithdrawalPrivateNoDiscountCalculator::class => [
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
        WithdrawalPrivateCalculator::class => [
            'enabled' => true,
            'extends' => WithdrawalPrivateNoDiscountCalculator::class,
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
        WithdrawalPrivateCustomCurrencyCalculator::class => [
            'enabled' => true,
            'extends' => WithdrawalPrivateCalculator::class,
            'requirements' => [
                'currency_code' => ['!=', function (Container $c) {
                    return $c->get('currency_default_code');
                }]
            ]
        ]
    ]
];
