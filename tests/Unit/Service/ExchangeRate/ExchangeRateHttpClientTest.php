<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service\ExchangeRate;

use DateTime;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateHttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ExchangeRateHttpClientTest extends TestCase
{
    public function testGetExchangeRateForDate(): void
    {
        $exchangeRateClient = new ExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response.json', '1');

        $exchangeRate = $exchangeRateClient->getExchangeRateForDate(new DateTime('01-01-2016'), 'EUR', 'JPY');
        $this->assertEquals(127.8684071530974, $exchangeRate);
    }

    public function testGetExchangeRateForDateThreeFailingTest(): void
    {
        $exchangeRateClient = new ExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response.json', '1');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->at(0))->method('warning')
            ->with('Error querying exchange rate data from remote API (try #1 of 3)');
        $logger->expects($this->at(1))->method('warning')
            ->with('Error querying exchange rate data from remote API (try #2 of 3)');
        $logger->expects($this->once())->method('critical')
            ->with('Error querying exchange rate data from remote API (try #3 of 3)');
        $exchangeRateClient->setLogger($logger);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error querying exchange rate data from remote API (try #3 of 3)');

        $exchangeRateClient->getExchangeRateForDate(new DateTime('01-01-2016'), 'EUR', 'USD');
    }

    public function testGetExchangeRateInvalidJsonReturned(): void
    {
        $exchangeRateClient = new ExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response.json', '1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode remote server response');

        $exchangeRateClient->getExchangeRateForDate(new DateTime('01-01-2016'), 'EUR', 'RUB');
    }

    public function testGetExchangeRateMissingDataInResponse(): void
    {
        $exchangeRateClient = new ExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response.json', '1');

        $this->expectException(RuntimeException::class);

        try {
            $exchangeRateClient->getExchangeRateForDate(new DateTime('01-01-2016'), 'EUR', 'CAD');
        } catch (\Throwable $e) {
            $this->assertEquals(0, strpos($e->getMessage(), 'Invalid response format was provided from currency API: '));
            throw $e;
        }
    }
}
