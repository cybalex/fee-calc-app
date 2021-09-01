<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction;

use FeeCalcApp\DTO\TransactionDto;

class InMemoryTransactionStorage implements TransactionStorageInterface
{
    private array $transactions = [];

    public function add(TransactionDto $transactionDto): self
    {
        $this->transactions[] = $transactionDto;

        return $this;
    }

    public function getAll(): array
    {
        return $this->transactions;
    }
}
