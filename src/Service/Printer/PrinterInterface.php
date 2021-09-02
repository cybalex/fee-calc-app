<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Printer;

use FeeCalcApp\DTO\TransactionDto;

interface PrinterInterface
{
    public function print(string $fee, int $scale, TransactionDto $transactionDto): void;
}
