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
    private const URL_HOST = 'http://api.currencylayer.com';
    private const PATH = '/live';
    private const API_KEY = '6ba50a7460abb5dacb95c02de8caa194';
    private const USD_CODE = Currency::USD_CODE;

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
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
            'access_key' => self::API_KEY,
            'currencies' => implode(',', [$currency1, $currency2]),
            'format' => 1,
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
            ],
        ];

        $response = $this->httpClient->get(self::URL_HOST.self::PATH, $queryParams, $options);

        try {
            $responseData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to decode response remote server');
        }

        if (
            !isset($responseData['quotes'][self::USD_CODE.$currency1])
            || !isset($responseData['quotes'][self::USD_CODE.$currency2])
        ) {
            throw new \RuntimeException('Invalid response format was provided from currency API: '.$response);
        }

        $currencySourceToUSD = (float) $responseData['quotes'][self::USD_CODE.$currency1];
        $currencyDestinationToUSD = (float) $responseData['quotes'][self::USD_CODE.$currency2];

        return $currencyDestinationToUSD / $currencySourceToUSD;
    }
}
