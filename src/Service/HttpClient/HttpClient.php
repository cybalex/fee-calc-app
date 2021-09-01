<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\HttpClient;

class HttpClient implements HttpClientInterface
{
    public function get(string $url, array $queryParams, array $options): string
    {
        $url .= '?'.http_build_query($queryParams);

        $options['http']['method'] = 'GET';

        $result = file_get_contents($url, false, stream_context_create($options));
        if (!$result) {
            throw new \RuntimeException('Failed to fetch data from remote URL');
        }

        return $result;
    }
}
