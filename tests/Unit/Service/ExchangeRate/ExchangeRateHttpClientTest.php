<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service\ExchangeRate;

use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateHttpClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ExchangeRateHttpClientTest extends TestCase
{
    private CurrencyConfig $currencyConfig;

    protected function setUp()
    {
        $this->currencyConfig = new CurrencyConfig(
            'USD',
            ['USD', 'EUR', 'CAD', 'RUB', 'JPY'],
            2,
            ['JPY' => 0]
        );
    }

    public function testGetExchangeRate(): void
    {
        $exchangeRateClient = new ExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response.json', '1', $this->currencyConfig);

        $exchangeRate = $exchangeRateClient->getExchangeRate( 'USD', 'JPY');
        $this->assertEquals(109.934499, $exchangeRate);
    }

    public function testGetExchangeRateInvalidJsonReturned(): void
    {
        $exchangeRateClient = new ExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response_malformed.json', '1', $this->currencyConfig);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error querying exchange rate data from remote API (try #3 of 3)');

        $exchangeRateClient->getExchangeRate( 'USD', 'RUB');
    }

    public function testGetExchangeRateMissingDataInResponse(): void
    {
        $exchangeRateClient = new ExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response1.json', '1', $this->currencyConfig);

        $this->expectException(RuntimeException::class);

        try {
            $exchangeRateClient->getExchangeRate('EUR', 'CAD');
        } catch (\Throwable $e) {
            $this->assertEquals(0, strpos($e->getMessage(), 'Invalid response format was provided from currency API: '));
            throw $e;
        }
    }
}
