<?php

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
    'currency_api_key' => '13cd8431d835173a67e1a98c6cbdd6d0',

    'deposit_fee_rate' => 0.0003,
    'private_withdrawal_free_weekly_amount' => 100000,
    'private_withdrawal_max_weekly_discounts_number' => 3,
    'withdrawal_private_fee_rate' => 0.003,
    'withdrawal_business_fee_rate' => 0.005,
    'log_file' => './var/log/logs.txt',
];
