<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction\Processor\Item;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\DTO\ProcessedTransactionDto;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\FeeCalculatorCollection;
use FeeCalcApp\Service\Transaction\TransactionContext;

class FeeCalculationItem implements TransactionProcessorItemInterface
{
    /**
     * @var FeeCalculatorInterface[]
     */
    private array $feeCalculators;

    private int $priority;

    public function __construct(FeeCalculatorCollection $feeCalculatorCollection, int $priority)
    {
        $this->feeCalculators = $feeCalculatorCollection->get();
        $this->priority = $priority;
    }

    public function process(TransactionDto $transactionDto, TransactionContext $context): void
    {
        foreach ($this->feeCalculators as $feeCalculator) {
            if (!$feeCalculator->isApplicable($transactionDto)) {
                continue;
            }

            $feeAmount = $feeCalculator->calculate($transactionDto);
            $context->setCurrentProcessedTransaction(
                new ProcessedTransactionDto($transactionDto, $feeAmount)
            );
        }
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
