<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction;

use FeeCalcApp\DTO\ProcessedTransactionDto;

class TransactionContext
{
    private ?ProcessedTransactionDto $currentProcessedTransaction = null;

    public function setCurrentProcessedTransaction(ProcessedTransactionDto $processedTransaction)
    {
        $this->currentProcessedTransaction = $processedTransaction;
    }

    public function getCurrentProcessedTransaction(): ?ProcessedTransactionDto
    {
        return $this->currentProcessedTransaction;
    }
}
