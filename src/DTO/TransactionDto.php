<?php

declare(strict_types=1);

namespace FeeCalcApp\DTO;

class TransactionDto extends AbstractTransaction
{
    public const CLIENT_TYPE_PRIVATE = 'private';
    public const CLIENT_TYPE_BUSINESS = 'business';

    public const OPERATION_TYPE_WITHDRAW = 'withdraw';
    public const OPERATION_TYPE_DEPOSIT = 'deposit';

    public function __construct(int $userId, string $clientType, \DateTime $date, string $currencyCode, int $amount, string $operationType)
    {
        $this->userId = $userId;
        $this->clientType = $clientType;
        $this->date = $date;
        $this->currencyCode = $currencyCode;
        $this->amount = $amount;
        $this->operationType = $operationType;
        $this->id = uniqid('', true);
    }
}
