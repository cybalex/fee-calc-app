<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use DateTime;
use FeeCalcApp\Config\AppConfig;
use FeeCalcApp\Service\Validation\Validator as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class TransactionRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^[1-9][0-9]*$/", message="Invalid value of userId field was provided")
     */
    private ?string $userId = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getSupportedClientTypes", message="Unsupported value {{ value }} was provided. Supported values are {{ choices }}")
     */
    private ?string $clientType = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getSupportedOperationTypes", message="Unsupported value {{ value }} was provided. Supported values are {{ choices }}")
     */
    private ?string $operationType = null;

    /**
     * @Assert\NotBlank()
     * @AppAssert\DateTimeFormat(callback="isValidDateFormat")
     */
    private ?string $date = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getSupportedCurrencyCodes", message="Unsupported value {{ value }} was provided. Supported values are {{ choices }}")
     */
    private ?string $currencyCode = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(pattern="/^(0|[1-9]\d*)(.\d+)?$/", message="Transaction amount in wrong format was provided")
     */
    private ?string $amount = null;

    private array $supportedClientTypes = [];
    private array $supportedOperationTypes = [];
    private array $supportedCurrencyCodes = [];
    private string $supportedDateFormat;

    public function __construct(AppConfig $appConfig)
    {
        $this->supportedClientTypes = $appConfig->getSupportedClientTypes();
        $this->supportedOperationTypes = $appConfig->getSupportedOperationTypes();
        $this->supportedCurrencyCodes = $appConfig->getCurrencyConfig()->getSupportedCurrencies();
        $this->supportedDateFormat = $appConfig->getDateFormat();
    }

    public function getSupportedClientTypes(): array
    {
        return $this->supportedClientTypes;
    }

    public function getSupportedOperationTypes(): array
    {
        return $this->supportedOperationTypes;
    }

    public function getSupportedCurrencyCodes(): array
    {
        return $this->supportedCurrencyCodes;
    }

    public function isValidDateFormat(string $value): bool
    {
        return !(false === DateTime::createFromFormat($this->supportedDateFormat, $value));
    }

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
