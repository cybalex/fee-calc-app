<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction;

use FeeCalcApp\DTO\ProcessedTransactionDto;

interface TransactionStorageInterface
{
    public function add(ProcessedTransactionDto $processedTransactionDto): self;

    public function getAll(): array;

    public function get(string $key): ?ProcessedTransactionDto;
}
