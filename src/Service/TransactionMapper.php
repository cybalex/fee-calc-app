<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use DateTime;
use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\DTO\TransactionDto;

class TransactionMapper
{
    public function __construct(private string $dateFormat, private Math $math, private CurrencyConfig $currencyConfig)
    {
    }

    public function map(TransactionRequest $transactionRequest): TransactionDto
    {
        $dateTime = DateTime::createFromFormat($this->dateFormat, $transactionRequest->getDate());
        $pennyAmount = $this->math->mul(
            $transactionRequest->getAmount(),
            (string) pow(10, $this->currencyConfig->getCurrencyScale($transactionRequest->getCurrencyCode()))
        );

        return new TransactionDto(
            (int) $transactionRequest->getUserId(),
            $transactionRequest->getClientType(),
            $dateTime,
            $transactionRequest->getCurrencyCode(),
            (int) $pennyAmount,
            $transactionRequest->getOperationType()
        );
    }
}
