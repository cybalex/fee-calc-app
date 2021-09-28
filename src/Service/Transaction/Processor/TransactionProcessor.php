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

    public function __construct(iterable $processorItems)
    {//var_export(iterator_to_array($processorItems, true));die;
        foreach ($processorItems as $processorItem) {
            //var_export($processorItem);
            $this->addProcessor($processorItem);
        }

        usort(
            $this->processors,
            function (ProcessorItem $p1, ProcessorItem $p2) {
                return $p1->getPriority() <=> $p2->getPriority();
            }
        );
    }

    public function process(TransactionDto $transactionDto, TransactionContext $context): bool
    {
        foreach ($this->processors as $processor) {
            if (!$processor->process($transactionDto, $context)) {
                return false;
            }
        }

        return true;
    }

    private function addProcessor(ProcessorItem $processorItem): void
    {
        $this->processors[] = $processorItem;
    }
}
