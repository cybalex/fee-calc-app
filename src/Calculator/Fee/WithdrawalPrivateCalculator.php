<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Fee;

use FeeCalcApp\Calculator\Config\Params\Item\FeeRateParameter;
use FeeCalcApp\Calculator\Config\Params\Item\FreeWeeklyTransactionAmount;
use FeeCalcApp\Calculator\FeeDiscountCalculatorInterface;
use FeeCalcApp\Calculator\Filter\FilterInterface;
use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\Dto\TransactionDto;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\TransactionHistoryManager;

class WithdrawalPrivateCalculator extends SimpleCalculator implements FeeDiscountCalculatorInterface, FilterInterface
{
    protected CurrencyConfig $currencyConfig;
    private TransactionHistoryManager $transactionHistoryManager;

    public function __construct(
        Math $math,
        TransactionHistoryManager $transactionHistoryManager,
        CurrencyConfig $currencyConfig
    ) {
        parent::__construct($math);

        $this->transactionHistoryManager = $transactionHistoryManager;
        $this->currencyConfig = $currencyConfig;
    }

    public function calculate(TransactionDto $transactionDto): string
    {
        $maxFeeInCurrency = parent::calculate($transactionDto);

        return $this->math->sub($maxFeeInCurrency, $this->calculateDiscount($transactionDto, $maxFeeInCurrency));
    }

    public function calculateDiscount(TransactionDto $transactionDto, string $maxFeeInCurrency): string
    {
        $transactionsWithinAWeek = $this->transactionHistoryManager->getUserTransactionsWithinAWeek($transactionDto);

        $totalAmountWithdrawalsForAWeek = $this->transactionHistoryManager
            ->getUserTransactionsTotalAmount($transactionsWithinAWeek, $this->currencyConfig->getDefaultCurrencyCode());

        $maxDiscountInTransactionCurrency = $this->getDiscountInTransactionCurrency($transactionDto, $totalAmountWithdrawalsForAWeek);

        return $this->math->floor($this->math->min($maxDiscountInTransactionCurrency, $maxFeeInCurrency));
    }

    protected function getDiscountInTransactionCurrency(
        TransactionDto $transactionDto,
        string $totalAmountWithdrawalsForAWeek
    ): string {
        return $this->math->mul(
            (string) $this->paramBag->getParam(FeeRateParameter::PARAM_NAME)->getValue(),
            $this->math->max(
                $this->math->sub(
                    (string) $this->paramBag->getParam(FreeWeeklyTransactionAmount::PARAM_NAME)->getValue(),
                    $totalAmountWithdrawalsForAWeek
                ),
                '0'
            )
        );
    }
}
