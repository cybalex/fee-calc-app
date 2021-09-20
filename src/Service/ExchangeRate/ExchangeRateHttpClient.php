<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\ExchangeRate;

use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\Exception\BadResponseException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class ExchangeRateHttpClient implements ExchangeRateClientInterface
{
    private const MAX_RETRY_COUNT = 3;
    private const RETRY_INTERVAL_SEC = 1;

    private string $currencyApiUrl;
    private string $currencyApiKey;
    private LoggerInterface $logger;
    private CurrencyConfig $currencyConfig;
    private array $exchangeRates = [];

    public function __construct(
        string $currencyApiUrl,
        string $currencyApiKey,
        CurrencyConfig $currencyConfig,
        LoggerInterface $logger
    ) {
        $this->currencyApiUrl = $currencyApiUrl;
        $this->currencyApiKey = $currencyApiKey;
        $this->currencyConfig = $currencyConfig;
        $this->logger = $logger;
    }

    /**
     * Get the exchange rate $currency1 / $currency2.
     *
     * @param string $currency1 Currency code. For example: 'USD'
     * @param string $currency2 Currency code. For example: 'EUR'
     */
    public function getExchangeRate(string $currency1, string $currency2): float
    {
        if (empty($this->exchangeRates)) {
            $this->exchangeRates = $this->getExchangeRates();
        }

        return $this->exchangeRates[$currency1.$currency2];
    }

    private function getExchangeRates(): array
    {
        $queryParams = [
            'access_key' => $this->currencyApiKey,
            'currencies' => implode(',', $this->currencyConfig->getSupportedCurrencies()),
            'format' => 1,
        ];

        $url = $this->currencyApiUrl.'?'.http_build_query($queryParams);

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'GET',
            ],
        ];

        $responseData = $this->sendRequest($url, $options);

        $exchangeRates = [];

        $quotes = $responseData['quotes'];

        $defaultCurrencyCode = $this->currencyConfig->getDefaultCurrencyCode();

        foreach ($this->currencyConfig->getSupportedCurrencies() as $supportedCurrencyCode) {
            if (!isset($quotes[CurrencyConfig::USD_CODE.$supportedCurrencyCode])) {
                throw new \RuntimeException('Invalid response format was provided from currency API: '.json_encode($responseData));
            }

            $quoteName = $defaultCurrencyCode.$supportedCurrencyCode;
            $usdToDefaultCurrencyRate = (float) $quotes[CurrencyConfig::USD_CODE.$defaultCurrencyCode];
            $usdToSupportedCurrency = (float) $quotes[CurrencyConfig::USD_CODE.$supportedCurrencyCode];

            $exchangeRates[$quoteName] = $usdToSupportedCurrency / $usdToDefaultCurrencyRate;
        }

        return $exchangeRates;
    }

    private function sendRequest(string $url, array $options, int $try = 1): array
    {
        try {
            $response = file_get_contents($url, false, stream_context_create($options));
            $responseData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (isset($responseData['success']) && $responseData['success'] === 'false') {
                throw new BadResponseException($responseData['error']['info'] ?? 'Error response');
            }

            return $responseData;
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to decode remote server response');
        } catch (Throwable $e) {
            $errorMessage = sprintf(
                'Error querying exchange rate data from remote API (try #%d of %d)',
                $try,
                self::MAX_RETRY_COUNT
            );

            $context = [
                'url' => $url,
                'method' => 'GET',
                'headers' => $options['http']['header'],
                'message' => $e->getMessage(),
            ];

            if (isset($response)) {
                $context['response'] = var_export($response);
            }

            if ($try === self::MAX_RETRY_COUNT) {
                $this->logger->critical($errorMessage, $context);
                throw new RuntimeException($errorMessage);
            }

            $this->logger->warning($errorMessage, $context);
            sleep(self::RETRY_INTERVAL_SEC * $try);
            $this->sendRequest($url, $options, $try + 1);
        }
    }
}
