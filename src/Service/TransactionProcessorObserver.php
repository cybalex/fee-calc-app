<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

class TransactionProcessorObserver implements \SplObserver
{
    private TransactionHistoryManager $transactionHistoryManager;

    public function __construct(TransactionHistoryManager $transactionHistoryManager)
    {
        $this->transactionHistoryManager = $transactionHistoryManager;
    }

    public function update(\SplSubject $subject)
    {
        if (!$subject instanceof TransactionProcessor) {
            return;
        }

        $transactionDto = $subject->getCurrentTransaction();
        $this->transactionHistoryManager->add($transactionDto);
    }
}
