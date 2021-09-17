<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Config\CurrencyConfig;
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
        CurrencyConfig $currencyConfig,
        int $freeWithdrawalWeeklyAmount
    ) {
        parent::__construct(
            $math,
            $transactionHistoryManager,
            $withdrawalFeeRate,
            $maxWeeklyDiscountsNumber,
            $currencyConfig,
            $freeWithdrawalWeeklyAmount
        );
        $this->exchangeRateClient = $exchangeRateClient;
    }

    protected function getDiscountInTransactionCurrency(
        TransactionDto $transactionDto,
        string $totalAmountWithdrawalsForAWeek
    ): string {
        $discountInDefaultCurrency = parent::getDiscountInTransactionCurrency($transactionDto, $totalAmountWithdrawalsForAWeek);
        $transactionCurrencyCode = $transactionDto->getCurrencyCode();

        return $this->math->div(
                $this->math->mul(
                    $discountInDefaultCurrency,
                    (string) $this->exchangeRateClient->getExchangeRateForDate(
                        $transactionDto->getDate(),
                        $this->currencyConfig->getDefaultCurrencyCode(),
                        $transactionDto->getCurrencyCode()
                    )),
                (string) pow(
                    10,
                    $this->currencyConfig->getCurrencyDefaultScale()
                    - $this->currencyConfig->getCurrencyScale($transactionCurrencyCode)
                )
            )
        ;
    }
}
