<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Config;

interface ConfigBuilderInterface
{
    public function getConfig(): array;
}
