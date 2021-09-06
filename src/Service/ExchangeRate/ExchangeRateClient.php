<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\ExchangeRate;

use FeeCalcApp\DTO\Currency;
use FeeCalcApp\Service\HttpClient\HttpClientInterface;

/**
 * @ToDo: add retry logic if request fails
 */
class ExchangeRateClient implements ExchangeRateClientInterface
{
    private HttpClientInterface $httpClient;

    private string $currencyApiUrl;
    private string $currencyApiKey;

    public function __construct(HttpClientInterface $httpClient, string $curencyApiUrl, string $currencyApiKey)
    {
        $this->httpClient = $httpClient;
        $this->currencyApiUrl = $curencyApiUrl;
        $this->currencyApiKey = $currencyApiKey;
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

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
            ],
        ];

        $response = $this->httpClient->get($this->currencyApiUrl, $queryParams, $options);

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
}
