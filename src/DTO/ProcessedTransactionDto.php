<?php

declare(strict_types=1);

namespace FeeCalcApp\DTO;

class ProcessedTransactionDto extends Transaction
{
    private string $fee;

    public function __construct(
        TransactionDto $transactionDto,
        string $fee
    ) {
        $this->userId = $transactionDto->getUserId();
        $this->clientType = $transactionDto->getClientType();
        $this->date = $transactionDto->getDate();
        $this->currency = $transactionDto->getCurrency();
        $this->amount = $transactionDto->getAmount();
        $this->operationType = $transactionDto->getOperationType();
        $this->id = $transactionDto->getId();
        $this->fee = $fee;
    }

    public function getFee(): string
    {
        return $this->fee;
    }
}
