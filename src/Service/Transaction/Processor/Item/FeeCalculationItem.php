<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction\Processor\Item;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\DTO\ProcessedTransactionDto;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\FeeCalculatorCollectionFactory;
use FeeCalcApp\Service\Transaction\TransactionContext;

class FeeCalculationItem implements TransactionProcessorItemInterface
{
    /**
     * @var FeeCalculatorInterface[]
     */
    private array $feeCalculators;

    private int $priority;

    public function __construct(FeeCalculatorCollectionFactory $feeCalculatorCollectionFactory, int $priority)
    {
        $this->feeCalculators = $feeCalculatorCollectionFactory->get();
        $this->priority = $priority;
    }

    public function process(TransactionDto $transactionDto, TransactionContext $context): bool
    {
        $isApplicable = false;
        foreach ($this->feeCalculators as $feeCalculator) {
            if (!$feeCalculator->isApplicable($transactionDto)) {
                continue;
            }

            $isApplicable = true;
            $feeAmount = $feeCalculator->calculate($transactionDto);
            $context->setCurrentProcessedTransaction(
                new ProcessedTransactionDto($transactionDto, $feeAmount)
            );
        }

        return $isApplicable;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
