<?php

declare(strict_types=1);

namespace FeeCalcApp\DTO;

use DateTime;

class TransactionDto extends AbstractTransaction
{
    public function __construct(
        int $userId,
        string $clientType,
        DateTime $date,
        string $currencyCode,
        int $amount,
        string $operationType
    ) {
        $this->userId = $userId;
        $this->clientType = $clientType;
        $this->date = $date;
        $this->currencyCode = $currencyCode;
        $this->amount = $amount;
        $this->operationType = $operationType;
        $this->id = uniqid('', true);
    }
}
