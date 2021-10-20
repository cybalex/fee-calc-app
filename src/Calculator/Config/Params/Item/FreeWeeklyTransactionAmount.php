<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config\Params\Item;

class FreeWeeklyTransactionAmount implements ParameterItemInterface
{
    public const PARAM_NAME = 'free_weekly_transaction_amount';

    /**
     * #[Assert\NotNull()]
     * #[Assert\Regex(pattern: "/^(0|[1-9]\d*)(.\d+)?$/", message: "Amount in wrong format was provided")].
     */
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
