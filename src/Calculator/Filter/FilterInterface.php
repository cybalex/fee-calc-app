<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

use FeeCalcApp\DTO\TransactionDto;

interface FilterInterface
{
    public function isApplicable(TransactionDto $transactionDto): bool;
}
