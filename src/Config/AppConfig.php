<?php

declare(strict_types=1);

namespace FeeCalcApp\Config;

class AppConfig
{
    private array $supportedClientTypes;
    private array $supportedOperationTypes;
    private CurrencyConfig $currencyConfig;
    private string $dateFormat;

    public function __construct(
        array $supportedClientTypes,
        array $supportedOperationTypes,
        CurrencyConfig $currencyConfig,
        string $dateFormat
    ) {
        $this->supportedClientTypes = $supportedClientTypes;
        $this->supportedOperationTypes = $supportedOperationTypes;
        $this->currencyConfig = $currencyConfig;
        $this->dateFormat = $dateFormat;
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
