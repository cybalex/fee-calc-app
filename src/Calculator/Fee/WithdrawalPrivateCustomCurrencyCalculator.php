<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\DTO\Currency;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateCustomCurrencyCalculator extends WithdrawalPrivateCalculator
{
    private ExchangeRateClientInterface $exchangeRateClient;

    public function __construct(
        Math $math,
        TransactionHistoryManager $transactionHistoryManager,
        float $withdrawalFeeRate,
        int $maxWeeklyDiscountsNumber,
        ExchangeRateClientInterface $exchangeRateClient,
        string $defaultCurrencyCode,
        int $freeWithdrawalWeeklyAmount
    ) {
        parent::__construct(
            $math,
            $transactionHistoryManager,
            $withdrawalFeeRate,
            $maxWeeklyDiscountsNumber,
            $defaultCurrencyCode,
            $freeWithdrawalWeeklyAmount
        );
        $this->exchangeRateClient = $exchangeRateClient;
    }

    public function isApplicable(TransactionDto $transactionDto): bool
    {
        if (
            !($transactionDto->getClientType() === TransactionDto::CLIENT_TYPE_PRIVATE
                && $transactionDto->getOperationType() === TransactionDto::OPERATION_TYPE_WITHDRAW
                && $transactionDto->getCurrency()->getCode() !== $this->defaultCurrencyCode)
        ) {
            return false;
        }

        $this->transactionsWithinAWeek = $this->transactionHistoryManager->getUserTransactionsWithinAWeek($transactionDto);

        return count($this->transactionsWithinAWeek) < $this->maxWeeklyDiscountsNumber;
    }

    protected function getDiscountInTransactionCurrency(
        TransactionDto $transactionDto,
        string $totalAmountWithdrawalsForAWeek
    ): string {
        $discountInDefaultCurrency = parent::getDiscountInTransactionCurrency($transactionDto, $totalAmountWithdrawalsForAWeek);

        return $this->math->div(
                $this->math->mul(
                    $discountInDefaultCurrency,
                    (string) $this->exchangeRateClient->getExchangeRateForDate(
                        $transactionDto->getDate(),
                        $this->defaultCurrencyCode,
                        $transactionDto->getCurrency()->getCode()
                    )),
                (string) pow(10, Currency::DEFAULT_SCALE - $transactionDto->getCurrency()->getScale())
            )
        ;
    }
}
