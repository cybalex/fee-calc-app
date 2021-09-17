<?php

declare(strict_types=1);

namespace FeeCalcApp\Config;

use InvalidArgumentException;

class CurrencyConfig
{
    public const USD_CODE = 'USD';
    public const EUR_CODE = 'EUR';
    public const JPY_CODE = 'JPY';

    private string $defaultCurrencyCode;
    private array $supportedCurrencies;
    private int $currencyDefaultScale;
    private array $currencyScaleMap;

    public function __construct(
        string $defaultCurrencyCode,
        array $supportedCurrencies,
        int $currencyDefaultScale,
        array $currencyScaleMap
    ) {
        $this->defaultCurrencyCode = $defaultCurrencyCode;
        $this->supportedCurrencies = $supportedCurrencies;
        $this->currencyDefaultScale = $currencyDefaultScale;
        $this->currencyScaleMap = $currencyScaleMap;
    }

    public function getDefaultCurrencyCode(): string
    {
        return $this->defaultCurrencyCode;
    }

    public function getCurrencyDefaultScale(): int
    {
        return $this->currencyDefaultScale;
    }

    public function getCurrencyScale(string $currencyCode): int
    {
        if (!in_array($currencyCode, $this->supportedCurrencies, true)) {
            throw new InvalidArgumentException(sprintf('Currency %s is not supported', $currencyCode));
        }

        return $this->currencyScaleMap[$currencyCode] ?? $this->currencyDefaultScale;
    }
}
