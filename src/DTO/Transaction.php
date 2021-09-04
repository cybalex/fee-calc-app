<?php

declare(strict_types=1);

namespace FeeCalcApp\DTO;

abstract class Transaction
{
    protected string $id;

    protected int $userId;

    protected string $clientType;

    protected \DateTime $date;

    protected Currency $currency;

    protected int $amount;

    protected string $operationType;

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getClientType(): string
    {
        return $this->clientType;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }
}
