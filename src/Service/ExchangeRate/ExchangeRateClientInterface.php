<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\ExchangeRate;

interface ExchangeRateClientInterface
{
    public function getExchangeRateForDate(\DateTime $date, string $currency1, string $currency2): float;
}
