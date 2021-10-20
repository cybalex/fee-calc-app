<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction\Processor\Item;

use FeeCalcApp\Calculator\FeeCalculatorInterface;
use FeeCalcApp\DTO\ProcessedTransactionDto;
use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\FeeCalculatorCollectionFactory;
use FeeCalcApp\Service\Transaction\TransactionContext;
use JetBrains\PhpStorm\Pure;

class FeeCalculationItem implements TransactionProcessorItemInterface
{
    /**
     * @var FeeCalculatorInterface[]
     */
    private array $feeCalculators;

    #[Pure]
    public function __construct(FeeCalculatorCollectionFactory $feeCalculatorCollectionFactory, private int $priority)
    {
        $this->feeCalculators = $feeCalculatorCollectionFactory->get();
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
