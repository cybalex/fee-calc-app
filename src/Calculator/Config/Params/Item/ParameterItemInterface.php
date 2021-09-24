<?php

namespace FeeCalcApp\Calculator\Config\Params\Item;

interface ParameterItemInterface
{
    /**
     * @return bool|array|int|float|string
     */
    public function getValue();

    /**
     * A name of a config parameter
     */
    public function getName(): string;
}
