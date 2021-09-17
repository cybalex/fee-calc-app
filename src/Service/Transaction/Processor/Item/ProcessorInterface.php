<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction\Processor\Item;

use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\Transaction\TransactionContext;

interface ProcessorInterface
{
    public function process(TransactionDto $transactionDto, TransactionContext $context): bool;
}
