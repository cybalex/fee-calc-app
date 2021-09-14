<?php

use DI\Container;

return [
    'supported_operation_types' => [
        'withdraw',
        'deposit'
    ],
    'supported_client_types' => [
        'private',
        'business',
    ],

    'currency_default_code' => 'EUR',
    'supported_currency_codes' => [
        'EUR',
        'USD',
        'JPY',
    ],
    'currency_default_scale' => 2,
    'currency_scale_map' => [
        'JPY' => 0,
    ],

    'date_format' => 'Y-m-d',

    'logs_date_format' => 'Y-m-d H:i:s',
    'currency_api_url' => 'http://api.currencylayer.com/live',
    'currency_api_key' => '2623fe61c14e8d237ae2a01361603707',

    'deposit_fee_rate' => 0.0003,
    'private_withdrawal_free_weekly_amount' => 100000,
    'private_withdrawal_max_weekly_discounts_number' => 3,
    'withdrawal_private_fee_rate' => 0.003,
    'withdrawal_business_fee_rate' => 0.005,
    'log_file' => './var/log/logs.txt',

    // under construction

//    'fee_calculation_config_setup' => [
//        'deposit' => [
//            'enabled' => true,
//            'params' => [
//                'fee_rate' => function(Container $c) {
//                    return $c->get('deposit_fee_rate');
//                },
//            ],
//            'requirements' => [
//                'operation_type' => 'deposit',
//            ]
//        ],
//        'withdrawal_business' => [
//            'enabled' => true,
//            'params' => [
//                'fee_rate' => function(Container $c) {
//                    return $c->get('withdrawal_business_fee_rate');
//                },
//            ],
//            'requirements' => [
//                'client_type' => 'business',
//                'operation_type' => 'withdrawal',
//            ]
//        ],
//        'withdrawal_private_no_discount' => [
//            'enabled' => true,
//            'params' => [
//                'operation_type' => 'withdrawal',
//                'client_type' => 'private',
//                'fee_rate' => function(Container $c) {
//                    return $c->get('withdrawal_private_fee_rate');
//                },
//                'max_weekly_discounts_number' => function (Container $c) {
//                    return $c->get('private_withdrawal_max_weekly_discounts_number');
//                },
//            ],
//            'requirements' => [
//                'weekly_transactions' => ['>=', function (Container $c) {
//                    return $c->get('private_withdrawal_max_weekly_discounts_number');
//                }],
//                'currency_code' => function (Container $c) {
//                    return $c->get('currency_default_code');
//                }
//            ],
//        ],
//        'withdrawal_private' => [
//            'extends' => 'withdrawal_private_no_discount',
//            'props' => [
//                'free_weekly_transaction_amount' => function (Container $c) {
//                    return $c->get('private_withdrawal_free_weekly_amount');
//                }
//            ],
//            'requirements' => [
//                'weekly_transactions' => ['<', function (Container $c) {
//                    return $c->get('private_withdrawal_max_weekly_discounts_number');
//                }],
//            ]
//        ],
//        'withdrawal_private_custom_currency' => [
//            'extends' => 'withdrawal_private',
//            'requirements' => [
//                'currency_code' => ['!=', function (Container $c) {
//                    return $c->get('currency_default_code');
//                }]
//            ]
//        ]
//    ]
];
