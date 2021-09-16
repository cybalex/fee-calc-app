<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service\ExchangeRate;

use DateTime;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateHttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ExchangeRateHttpClientTest extends TestCase
{
    public function testGetExchangeRateForDate(): void
    {
        $exchangeRateClient = new ExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response.json', '1');

        $logger = $this->createMock(LoggerInterface::class);
        $exchangeRateClient->setLogger($logger);

        $exchangeRate = $exchangeRateClient->getExchangeRateForDate(new DateTime('01-01-2016'), 'EUR', 'JPY');
        $this->assertEquals(127.8684071530974, $exchangeRate);
    }
}
