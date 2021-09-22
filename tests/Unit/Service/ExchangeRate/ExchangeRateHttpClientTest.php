<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service\ExchangeRate;

use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateHttpClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ExchangeRateHttpClientTest extends TestCase
{
    private CurrencyConfig $currencyConfig;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    protected function setUp()
    {
        $this->currencyConfig = new CurrencyConfig(
            'USD',
            ['USD', 'EUR', 'CAD', 'RUB', 'JPY'],
            2,
            ['JPY' => 0]
        );
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testGetExchangeRate(): void
    {
        $exchangeRateClient = $this->getExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response.json');

        $exchangeRate = $exchangeRateClient->getExchangeRate( 'USD', 'JPY');
        $this->assertEquals(109.934499, $exchangeRate);
    }

    public function testGetExchangeRateInvalidJsonReturned(): void
    {
        $exchangeRateClient = $this->getExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response_malformed.json',);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error querying exchange rate data from remote API (try #3 of 3)');

        $exchangeRateClient->getExchangeRate( 'USD', 'RUB');
    }

    public function testGetExchangeRateMissingDataInResponse(): void
    {
        $exchangeRateClient = $this->getExchangeRateHttpClient('tests/Unit/Service/ExchangeRate/api_response1.json');

        $this->expectException(RuntimeException::class);

        try {
            $exchangeRateClient->getExchangeRate('EUR', 'CAD');
        } catch (\Throwable $e) {
            $this->assertEquals(0, strpos($e->getMessage(), 'Invalid response format was provided from currency API: '));
            throw $e;
        }
    }

    private function getExchangeRateHttpClient(string $url): ExchangeRateHttpClient
    {
        return new ExchangeRateHttpClient($url, '1', $this->currencyConfig, $this->logger);
    }
}
