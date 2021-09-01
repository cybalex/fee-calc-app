<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use FeeCalcApp\DTO\Currency;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Exception\CurrencyNotFoundException;
use FeeCalcApp\Exception\TransactionException;

class TransactionBuilder
{
    private ?int $userId = null;

    private int $amount;

    private Currency $currency;

    private \DateTime $date;

    private string $clientType;

    private string $operationType;

    public function setUserId(int $userId): self
    {
        if ($userId === 0) {
            throw new TransactionException('userId');
        }

        $this->userId = $userId;

        return $this;
    }

    public function setCurrencyAmount(string $amount, string $currencyCode): self
    {
        try {
            $currency = new Currency($currencyCode);
            $pennyAmount = (int) $amount * pow(10, $currency->getScale());
        } catch (CurrencyNotFoundException $e) {
            throw new TransactionException('currency', $e);
        }

        if ($pennyAmount === 0) {
            throw new TransactionException('amount');
        }

        $this->amount = $pennyAmount;
        $this->currency = $currency;

        return $this;
    }

    public function setOperationType(string $operationType): self
    {
        if (!in_array($operationType, TransactionDto::SUPPORTED_OPERATION_TYPES, true)) {
            throw new TransactionException('operationType');
        }

        $this->operationType = $operationType;

        return $this;
    }

    public function setClientType(string $clientType): self
    {
        if (!in_array($clientType, TransactionDto::SUPPORTED_CLIENT_TYPES, true)) {
            throw new TransactionException('clientType');
        }

        $this->clientType = $clientType;

        return $this;
    }

    public function setDate(string $date): self
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);

        if (!$dateTime) {
            throw new TransactionException('date');
        }

        $this->date = $dateTime;

        return $this;
    }

    public function build(): TransactionDto
    {
        return new TransactionDto(
            $this->userId,
            $this->clientType,
            $this->date,
            $this->currency,
            $this->amount,
            $this->operationType
        );
    }
}
