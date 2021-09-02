<?php

declare(strict_types=1);

namespace FeeCalcApp\DTO;

class TransactionDto
{
    public const CLIENT_TYPE_PRIVATE = 'private';
    public const CLIENT_TYPE_BUSINESS = 'business';
    public const SUPPORTED_CLIENT_TYPES = [
        self::CLIENT_TYPE_PRIVATE,
        self::CLIENT_TYPE_BUSINESS,
    ];

    public const OPERATION_TYPE_WITHDRAW = 'withdraw';
    public const OPERATION_TYPE_DEPOSIT = 'deposit';
    public const SUPPORTED_OPERATION_TYPES = [
        self::OPERATION_TYPE_WITHDRAW,
        self::OPERATION_TYPE_DEPOSIT,
    ];

    private string $id;

    private int $userId;

    private string $clientType;

    private \DateTime $date;

    private Currency $currency;

    private int $amount;

    private string $operationType;

    public function __construct(int $userId, string $clientType, \DateTime $date, Currency $currency, int $amount, string $operationType)
    {
        $this->userId = $userId;
        $this->clientType = $clientType;
        $this->date = $date;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->operationType = $operationType;
        $this->id = uniqid('', true);
    }

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
