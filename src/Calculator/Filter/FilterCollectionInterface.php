<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

interface FilterCollectionInterface
{
    public function addFilter(FilterInterface $filter): void;
}
