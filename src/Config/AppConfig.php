<?php

declare(strict_types=1);

namespace FeeCalcApp\Config;

class AppConfig
{
    public function __construct(
        private array $supportedClientTypes,
        private array $supportedOperationTypes,
        private CurrencyConfig $currencyConfig,
        private string $dateFormat
    ) {
    }

    public function getCurrencyConfig(): CurrencyConfig
    {
        return $this->currencyConfig;
    }

    public function getSupportedClientTypes(): array
    {
        return $this->supportedClientTypes;
    }

    public function getSupportedOperationTypes(): array
    {
        return $this->supportedOperationTypes;
    }

    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }
}
