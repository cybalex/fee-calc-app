<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service\ExchangeRate;

use FeeCalcApp\Service\ExchangeRate\ExchangeRateCacheProxy;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateHttpClient;
use PHPUnit\Framework\TestCase;

class ExchangeRateCacheProxyTest extends TestCase
{
    public function testUseCachedExchangeRates(): void
    {
        $date = new \DateTime('2016-01-01');
        $currency1 = 'EUR';
        $currency2 = 'USD';
        $exchangeRate = 1.31234;

        $exchangeRateClient = $this->createMock(ExchangeRateHttpClient::class);
        $exchangeRateClient->expects($this->once())->method('getExchangeRateForDate')
            ->with($date, $currency1, $currency2)->willReturn($exchangeRate);

        $exchangeRateCacheProxy = new ExchangeRateCacheProxy($exchangeRateClient);

        $this->assertEquals($exchangeRate, $exchangeRateCacheProxy->getExchangeRateForDate($date, $currency1, $currency2));
        $this->assertEquals($exchangeRate, $exchangeRateCacheProxy->getExchangeRateForDate($date, $currency1, $currency2));
    }
}
