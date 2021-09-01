<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Printer;

use FeeCalcApp\DTO\TransactionDto;

class PlainPrinter implements PrinterInterface
{
    public function print(int $fee, int $scale, TransactionDto $transactionDto = null): void
    {
        $fee = (float) $fee / (pow(10, $scale));

        echo number_format($fee, $scale, '.', '').PHP_EOL;
    }
}
