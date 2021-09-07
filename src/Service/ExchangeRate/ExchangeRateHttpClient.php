<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\ExchangeRate;

use FeeCalcApp\DTO\Currency;
use FeeCalcApp\Exception\BadResponseException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Throwable;

class ExchangeRateHttpClient implements ExchangeRateClientInterface
{
    private const MAX_RETRY_COUNT = 3;
    private const RETRY_INTERVAL_SEC = 1;

    private string $currencyApiUrl;
    private string $currencyApiKey;
    private LoggerInterface $logger;

    public function __construct(
        string $currencyApiUrl,
        string $currencyApiKey
    ) {
        $this->currencyApiUrl = $currencyApiUrl;
        $this->currencyApiKey = $currencyApiKey;
        $this->logger = new NullLogger();
    }

    /**
     * Calculates the exchange rate $currency1 / $currency2.
     *
     * @param \DateTime $date      exchange rate for the given day
     * @param string    $currency1 Currency code. For example: 'USD'
     * @param string    $currency2 Currency code. For example: 'EUR'
     */
    public function getExchangeRateForDate(\DateTime $date, string $currency1, string $currency2): float
    {
        $queryParams = [
            'access_key' => $this->currencyApiKey,
            'currencies' => implode(',', [$currency1, $currency2]),
            'format' => 1,
        ];

        $url = $this->currencyApiUrl.'?'.http_build_query($queryParams);

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'GET',
            ],
        ];

        $response = $this->sendRequest($url, $options);

        if (!$response) {
            throw new \RuntimeException('Failed to fetch data from remote URL');
        }

        try {
            $responseData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to decode remote server response');
        }

        if (
            !isset(
                $responseData['quotes'][Currency::USD_CODE.$currency1],
                $responseData['quotes'][Currency::USD_CODE.$currency2]
            )
        ) {
            throw new \RuntimeException('Invalid response format was provided from currency API: '.$response);
        }

        $currencySourceToUSD = (float) $responseData['quotes'][Currency::USD_CODE.$currency1];
        $currencyDestinationToUSD = (float) $responseData['quotes'][Currency::USD_CODE.$currency2];

        return $currencyDestinationToUSD / $currencySourceToUSD;
    }

    public function setLogger($logger): void
    {
        $this->logger = $logger;
    }

    private function sendRequest(string $url, array $options, int $try = 1): string
    {
        try {
            $response = file_get_contents($url, false, stream_context_create($options));

            if (!is_string($response)) {
                throw new BadResponseException('Unexpected response type has been received. String expected, %s was received');
            }

            return $response;
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
