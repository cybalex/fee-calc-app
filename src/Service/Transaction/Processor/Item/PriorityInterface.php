<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction\Processor\Item;

interface PriorityInterface
{
    public function getPriority(): int;
}
