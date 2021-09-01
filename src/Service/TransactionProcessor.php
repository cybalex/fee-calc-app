<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\Printer\PrinterInterface;
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

    private ?PrinterInterface $feePrinter;

    private TransactionDto $currentTransaction;

    public function __construct(FeeCalculatorCollection $feeCalculatorCollection, PrinterInterface $feePrinter)
    {
        $this->feeCalculators = $feeCalculatorCollection->get();
        $this->feePrinter = $feePrinter;
    }

    public function process(TransactionDto $transactionDto)
    {
        $this->currentTransaction = $transactionDto;

        foreach ($this->feeCalculators as $feeCalculator) {
            if (!$feeCalculator->isApplicable($transactionDto)) {
                continue;
            }

            // this piece of code got some room for improvement
            $feeAmount = $feeCalculator->calculate($transactionDto);
            $this->feePrinter->print($feeAmount, $transactionDto->getCurrency()->getScale(), $transactionDto);
            $this->notify();
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
