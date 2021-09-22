<?php

use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;
use FeeCalcApp\Stub\ExchangeRateClientStub;

$config = require __DIR__ . '/../prod/config.php';

return array_merge($config, [
    ExchangeRateClientInterface::class => function () {
        return new ExchangeRateClientStub();
    }
]);
