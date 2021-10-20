<?php

declare(strict_types=1);

namespace FeeCalcApp\DTO;

class ProcessedTransactionDto extends AbstractTransaction
{
    public function __construct(
        TransactionDto $transactionDto,
        private string $fee
    ) {
        $this->userId = $transactionDto->getUserId();
        $this->clientType = $transactionDto->getClientType();
        $this->date = $transactionDto->getDate();
        $this->currencyCode = $transactionDto->getCurrencyCode();
        $this->amount = $transactionDto->getAmount();
        $this->operationType = $transactionDto->getOperationType();
        $this->id = $transactionDto->getId();
    }

    public function getFee(): string
    {
        return $this->fee;
    }
}
