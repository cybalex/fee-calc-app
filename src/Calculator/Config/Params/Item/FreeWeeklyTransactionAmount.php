<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config\Params\Item;

class FreeWeeklyTransactionAmount implements ParameterItemInterface
{
    public const PARAM_NAME = 'free_weekly_transaction_amount';

    private float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getName(): string
    {
        return static::PARAM_NAME;
    }
}
