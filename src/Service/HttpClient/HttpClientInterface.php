<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\HttpClient;

interface HttpClientInterface
{
    public function get(string $url, array $queryParams, array $options): string;
}
