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
        ExchangeRateClientInterface $exchangeRateClient,
        CurrencyConfig $currencyConfig
    ) {
        parent::__construct(
            $math,
            $transactionHistoryManager,
            $currencyConfig,
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
                    (string) $this->exchangeRateClient->getExchangeRate(
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
