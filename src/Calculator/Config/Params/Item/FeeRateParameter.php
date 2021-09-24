<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config\Params\Item;

class FeeRateParameter implements ParameterItemInterface
{
    public const PARAM_NAME = 'fee_rate';

    private float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getName(): string
    {
        return self::PARAM_NAME;
    }
}
