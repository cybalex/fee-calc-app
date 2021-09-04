<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\DTO\ProcessedTransactionDto;
use FeeCalcApp\DTO\TransactionDto;
use SplObserver;

class TransactionProcessor implements \SplSubject
{
    /**
     * @var SplObserver[]
     */
    private array $observers = [];

    /**
     * @var FeeCalculatorInterface[]
     */
    private array $feeCalculators;

    private TransactionDto $currentTransaction;

    public function __construct(
        FeeCalculatorCollection $feeCalculatorCollection
    ) {
        $this->feeCalculators = $feeCalculatorCollection->get();
    }

    public function process(TransactionDto $transactionDto): ProcessedTransactionDto
    {
        $this->currentTransaction = $transactionDto;

        foreach ($this->feeCalculators as $feeCalculator) {
            if (!$feeCalculator->isApplicable($transactionDto)) {
                continue;
            }

            $feeAmount = $feeCalculator->calculate($transactionDto);
            $this->notify();

            return new ProcessedTransactionDto($transactionDto, $feeAmount);
        }
    }

    public function attach(SplObserver $observer)
    {
        $this->observers[] = $observer;
    }

    public function detach(SplObserver $observer)
    {
        $this->observers = array_filter($this->observers, function ($existingObserver) use ($observer) {
            return !($existingObserver === $observer);
        });
    }

    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function getCurrentTransaction(): TransactionDto
    {
        return $this->currentTransaction;
    }
}
