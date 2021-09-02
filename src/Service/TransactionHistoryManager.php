<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use FeeCalcApp\DTO\Currency;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Helper\DatetimeHelper;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;
use FeeCalcApp\Service\Transaction\TransactionStorageInterface;

class TransactionHistoryManager
{
    private ExchangeRateClientInterface $exchangeRateClient;

    private TransactionStorageInterface $transactionStorage;

    private DatetimeHelper $dateTimeHelper;

    public function __construct(
        ExchangeRateClientInterface $exchangeRateClient,
        TransactionStorageInterface $transactionStorageInterface,
        DatetimeHelper $dateTimeHelper
    ) {
        $this->exchangeRateClient = $exchangeRateClient;
        $this->transactionStorage = $transactionStorageInterface;
        $this->dateTimeHelper = $dateTimeHelper;
    }

    public function add(TransactionDto $transactionDto): self
    {
        $this->transactionStorage->add($transactionDto);

        return $this;
    }

    public function getUserTransactionsWithinAWeek(TransactionDto $transactionDto): array
    {
        return array_filter(
            $this->transactionStorage->getAll(), function (TransactionDto $transactionFromHistory) use ($transactionDto) {
                return $this->dateTimeHelper->datesAreWithinSameWeek($transactionDto->getDate(), $transactionFromHistory->getDate())
                        && $transactionDto->getUserId() === $transactionFromHistory->getUserId()
                        && $transactionDto->getOperationType() === $transactionFromHistory->getOperationType()
                        && $transactionDto->getDate() >= $transactionFromHistory->getDate()
                        && $transactionDto->getId() !== $transactionFromHistory->getId()
                ;
            });
    }

    /**
     * @param TransactionDto[] $transactions
     */
    public function getUserTransactionsTotalAmount(?array $transactions, string $inCurrency): int
    {
        $totalAmount = 0;

        foreach ($transactions as $transaction) {
            $transactionCurrencyCode = $transaction->getCurrency()->getCode();

            $transactionAmount = $transactionCurrencyCode === $inCurrency
                ? $transaction->getAmount()
                : $transaction->getAmount() / $this->exchangeRateClient->getExchangeRateForDate(
                    $transaction->getDate(),
                    $inCurrency,
                    $transactionCurrencyCode
                ) * pow(10, Currency::DEFAULT_SCALE - $transaction->getCurrency()->getScale())
            ;
            $totalAmount += (int) round($transactionAmount);
        }

        return $totalAmount;
    }
}
