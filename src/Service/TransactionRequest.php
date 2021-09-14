<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

class TransactionRequest
{
    private ?string $userId = null;

    private ?string $clientType;

    private ?string $operationType;

    private ?string $date;

    private ?string $currencyCode;

    private ?string $amount;

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setUserId(?string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setClientType(?string $clientType): self
    {
        $this->clientType = $clientType;

        return $this;
    }

    public function getClientType(): ?string
    {
        return $this->clientType;
    }

    public function setOperationType(?string $operationType): self
    {
        $this->operationType = $operationType;

        return $this;
    }

    public function getOperationType(): ?string
    {
        return $this->operationType;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setCurrencyCode(?string $currencyCode): self
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'client_type' => $this->clientType,
            'operation_type' => $this->operationType,
            'date' => $this->date,
            'currency_code' => $this->currencyCode,
            'amount' => $this->amount,
        ];
    }
}
