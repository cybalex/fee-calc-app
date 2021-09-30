<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config\Params\Item;

use Symfony\Component\Validator\Constraints as Assert;

class FeeRateParameter implements ParameterItemInterface
{
    public const PARAM_NAME = 'fee_rate';

    /**
     * @Assert\NotNull()
     * @Assert\Regex(pattern="/^(0|[1-9]\d*)(.\d+)?$/", message="Amount in wrong format was provided")
     */
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
