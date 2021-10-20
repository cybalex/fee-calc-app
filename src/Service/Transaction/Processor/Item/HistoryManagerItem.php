<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction\Processor\Item;

use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\Transaction\TransactionContext;
use FeeCalcApp\Service\TransactionHistoryManager;

class HistoryManagerItem implements TransactionProcessorItemInterface
{
    public function __construct(
        private TransactionHistoryManager $transactionHistoryManager,
        private int $priority
    ) {
    }

    public function process(TransactionDto $transactionDto, TransactionContext $context): bool
    {
        $this->transactionHistoryManager->add($context->getCurrentProcessedTransaction());

        return true;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
