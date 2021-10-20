<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config\Params\Item;

interface ParameterItemInterface
{
    public function getValue(): bool|array|int|float|string;

    /**
     * A name of a config parameter.
     */
    public function getName(): string;
}
