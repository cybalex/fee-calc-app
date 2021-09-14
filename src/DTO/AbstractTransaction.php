<?php

declare(strict_types=1);

namespace FeeCalcApp\DTO;

use DateTime;

abstract class AbstractTransaction implements TransactionInterface
{
    protected string $id;

    protected int $userId;

    protected string $clientType;

    protected DateTime $date;

    protected int $amount;

    protected string $operationType;

    protected string $currencyCode;

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

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
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
