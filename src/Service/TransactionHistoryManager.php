<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\DTO\ProcessedTransactionDto;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Helper\DatetimeHelper;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;
use FeeCalcApp\Service\Transaction\TransactionStorageInterface;

class TransactionHistoryManager
{
    private ExchangeRateClientInterface $exchangeRateClient;

    private TransactionStorageInterface $transactionStorage;

    private DatetimeHelper $dateTimeHelper;

    private Math $math;
    private CurrencyConfig $currencyConfig;

    public function __construct(
        ExchangeRateClientInterface $exchangeRateClient,
        TransactionStorageInterface $transactionStorageInterface,
        DatetimeHelper $dateTimeHelper,
        Math $math,
        CurrencyConfig $currencyConfig
    ) {
        $this->exchangeRateClient = $exchangeRateClient;
        $this->transactionStorage = $transactionStorageInterface;
        $this->dateTimeHelper = $dateTimeHelper;
        $this->math = $math;
        $this->currencyConfig = $currencyConfig;
    }

    public function add(ProcessedTransactionDto $processedTransactionDto): self
    {
        $this->transactionStorage->add($processedTransactionDto);

        return $this;
    }

    public function get(string $key): ?ProcessedTransactionDto
    {
        return $this->transactionStorage->get($key);
    }

    public function getAll()
    {
        return $this->transactionStorage->getAll();
    }

    public function getUserTransactionsWithinAWeek(TransactionDto $transactionDto): array
    {
        return array_filter(
            $this->transactionStorage->getAll(), function (ProcessedTransactionDto $transactionFromHistory) use ($transactionDto) {
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
    public function getUserTransactionsTotalAmount(array $transactions, string $inCurrency): string
    {
        $totalAmount = '0';

        foreach ($transactions as $transaction) {
            $transactionCurrencyCode = $transaction->getCurrencyCode();

            $transactionAmount = $transactionCurrencyCode === $inCurrency
                ? (string) $transaction->getAmount()
                : $this->math->mul(
                    $this->math->div(
                        (string) $transaction->getAmount(),
                        (string) $this->exchangeRateClient->getExchangeRateForDate(
                            $transaction->getDate(),
                            $inCurrency,
                            $transactionCurrencyCode
                        )
                    ),
                    (string) pow(
                        10,
                        $this->currencyConfig->getCurrencyDefaultScale()
                        - $this->currencyConfig->getCurrencyScale($transactionCurrencyCode)
                    )
                )
            ;
            $totalAmount = $this->math->add($totalAmount, $transactionAmount);
        }

        return $totalAmount;
    }
}
