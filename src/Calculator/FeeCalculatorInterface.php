<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator;

use FeeCalcApp\Calculator\Filter\FilterCollectionInterface;
use FeeCalcApp\Calculator\Filter\FilterInterface;
use FeeCalcApp\DTO\TransactionDto;

interface FeeCalculatorInterface extends FilterCollectionInterface, FilterInterface
{
    public function calculate(TransactionDto $transactionDto): string;
}
