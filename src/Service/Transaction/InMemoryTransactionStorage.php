<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction;

use FeeCalcApp\DTO\ProcessedTransactionDto;

class InMemoryTransactionStorage implements TransactionStorageInterface
{
    private array $transactions = [];

    public function add(ProcessedTransactionDto $processedTransactionDto): self
    {
        $this->transactions[$processedTransactionDto->getId()] = $processedTransactionDto;

        return $this;
    }

    public function getAll(): array
    {
        return $this->transactions;
    }

    public function get(string $key): ?ProcessedTransactionDto
    {
        return $this->transactions[$key] ?? null;
    }
}
