<?php

declare(strict_types=1);

namespace FeeCalcApp\Stub;

use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;

class ExchangeRateClientStub implements ExchangeRateClientInterface
{
    public function getExchangeRateForDate(\DateTime $date, string $currency1, string $currency2): float
    {
        if ($currency1 === 'EUR' && $currency2 === 'USD') {
            return 1.1497;
        }

        if ($currency1 === 'EUR' && $currency2 === 'JPY') {
            return 129.53;
        }

        return 1.00;
    }
}
