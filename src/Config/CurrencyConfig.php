<?php

declare(strict_types=1);

namespace FeeCalcApp\Config;

use InvalidArgumentException;

class CurrencyConfig
{
    public const USD_CODE = 'USD';
    public const EUR_CODE = 'EUR';
    public const JPY_CODE = 'JPY';

    public function __construct(
        private string $defaultCurrencyCode,
        private array $supportedCurrencies,
        private int $currencyDefaultScale,
        private array $currencyScaleMap
    ) {
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

    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }
}
