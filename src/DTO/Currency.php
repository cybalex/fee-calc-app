<?php

declare(strict_types=1);

namespace FeeCalcApp\DTO;

use FeeCalcApp\Exception\CurrencyNotFoundException;

class Currency
{
    public const USD_CODE = 'USD';
    public const EUR_CODE = 'EUR';
    public const JPY_CODE = 'JPY';

    public const AVAILABLE_CURRENCIES = [
        self::USD_CODE,
        self::EUR_CODE,
        self::JPY_CODE,
    ];

    public const DEFAULT_SCALE = 2;

    private const CURRENCY_CODE_CUSTOM_SCALE_MAP = [
        'JPY' => 0,
    ];

    private ?string $code;

    private ?int $scale;

    public function __construct(string $code)
    {
        $this->code = $code;

        if (!in_array($code, self::AVAILABLE_CURRENCIES, true)) {
            throw new CurrencyNotFoundException(sprintf('Currency code "%s" is invalid or not supported by the fee app.', $code));
        }

        $this->scale = array_key_exists($code, self::CURRENCY_CODE_CUSTOM_SCALE_MAP)
            ? self::CURRENCY_CODE_CUSTOM_SCALE_MAP[$code]
            : self::DEFAULT_SCALE;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }
}
