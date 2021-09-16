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
    'currency_api_url' => 'http://api.currencylayer.com/historical',
    'currency_api_key' => '2623fe61c14e8d237ae2a01361603707',

    'deposit_fee_rate' => 0.0003,
    'private_withdrawal_free_weekly_amount' => 100000,
    'private_withdrawal_max_weekly_discounts_number' => 3,
    'withdrawal_private_fee_rate' => 0.003,
    'withdrawal_business_fee_rate' => 0.005,
    'log_file' => './var/log/logs.txt',
];
