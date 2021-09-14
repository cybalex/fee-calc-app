<?php

declare(strict_types=1);

namespace FeeCalcApp\Service\Transaction\Processor;

use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\Transaction\Processor\Item\ProcessorInterface;
use FeeCalcApp\Service\Transaction\Processor\Item\TransactionProcessorItemInterface as ProcessorItem;
use FeeCalcApp\Service\Transaction\TransactionContext;

class TransactionProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorItem[]
     */
    private array $processors = [];

    public function __construct(array $processorItems)
    {
        foreach ($processorItems as $processorItem) {
            $this->addProcessor($processorItem);
        }

        usort(
            $this->processors,
            fn (ProcessorItem $p1, ProcessorItem $p2) => $p1->getPriority() <=> $p2->getPriority()
        );
    }

    public function process(TransactionDto $transactionDto, TransactionContext $context): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($transactionDto, $context);
        }
    }

    private function addProcessor(ProcessorItem $processorItem): void
    {
        $this->processors[] = $processorItem;
    }
}
