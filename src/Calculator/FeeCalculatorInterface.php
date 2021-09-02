<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator;

use FeeCalcApp\DTO\TransactionDto;

interface FeeCalculatorInterface
{
    public function calculate(TransactionDto $transactionDto): string;

    public function isApplicable(TransactionDto $transactionDto);
}
