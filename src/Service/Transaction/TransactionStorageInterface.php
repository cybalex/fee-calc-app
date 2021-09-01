<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction;

use FeeCalcApp\DTO\TransactionDto;

interface TransactionStorageInterface
{
    public function add(TransactionDto $transactionDto): self;

    public function getAll(): array;
}
