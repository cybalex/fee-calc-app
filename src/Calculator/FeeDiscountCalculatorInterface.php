<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator;

use FeeCalcApp\DTO\TransactionDto;

interface FeeDiscountCalculatorInterface
{
    public function calculateDiscount(TransactionDto $transactionDto, string $maxFeeInCurrency): string;
}
