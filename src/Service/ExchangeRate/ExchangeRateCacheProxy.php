<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\ExchangeRate;

class ExchangeRateCacheProxy implements ExchangeRateClientInterface
{
    private const DATE_FORMAT = 'Y-m-d';
    private array $exchangeRates = [];

    private ?ExchangeRateClientInterface $exchangeRateClient;

    public function __construct(ExchangeRateClientInterface $exchangeRateClient)
    {
        $this->exchangeRateClient = $exchangeRateClient;
    }

    public function getExchangeRateForDate(\DateTime $date, string $currency1, string $currency2): float
    {
        $cacheKey = $this->getCacheKey($date, $currency1, $currency2);

        if (isset($this->exchangeRates[$cacheKey])) {
            return $this->exchangeRates[$cacheKey];
        }

        $exchangeRate = $this->exchangeRateClient->getExchangeRateForDate($date, $currency1, $currency2);

        // a temporary workaround not to run into too many requests issue with free access api key.
        // Add retry logic to ExchangeRateClient instead
        sleep(2);

        $this->exchangeRates[$cacheKey] = $exchangeRate;

        return $exchangeRate;
    }

    private function getCacheKey(\DateTime $date, string $currency1, string $currency2): string
    {
        return sprintf('%s%s%s', $date->format(self::DATE_FORMAT), $currency1, $currency2);
    }
}
