<?php

declare(strict_types=1);

namespace FeeCalcApp\Service;

use FeeCalcApp\DTO\TransactionDto;
use FeeCalcApp\Service\Transaction\Processor\TransactionProcessor;
use FeeCalcApp\Service\Transaction\TransactionContext;
use FeeCalcApp\Service\Validation\TransactionRequestValidator;
use Psr\Log\LoggerInterface;

class TransactionHandler
{
    private array $transactionCollection = [];

    private TransactionRequestValidator $transactionRequestValidator;
    private TransactionMapper $transactionMapper;
    private TransactionProcessor $transactionProcessor;
    private LoggerInterface $logger;
    private array $transactionOriginalOrder;

    public function __construct(
        TransactionRequestValidator $transactionRequestValidator,
        TransactionMapper $transactionMapper,
        TransactionProcessor $transactionProcessor,
        LoggerInterface $logger
    ) {
        $this->transactionRequestValidator = $transactionRequestValidator;
        $this->transactionMapper = $transactionMapper;
        $this->transactionProcessor = $transactionProcessor;
        $this->logger = $logger;
    }

    public function addTransaction(TransactionRequest $transactionRequest): self
    {
        $constraintViolationList = $this->transactionRequestValidator->validate($transactionRequest);
        if ($constraintViolationList->count() > 0) {
            foreach ($constraintViolationList as $constraintViolation) {
                $this->logger->warning(
                    'Failed to process transaction data',
                    [
                        'message' => $constraintViolation->getMessage(),
                        'invalid_value' => $constraintViolation->getInvalidValue(),
                        'transaction_data' => $transactionRequest->toArray(),
                    ]
                );
            }

            return $this;
        }

        $transactionDto = $this->transactionMapper->map($transactionRequest);
        $this->transactionCollection[] = $transactionDto;
        $this->transactionOriginalOrder[] = $transactionDto->getId();

        return $this;
    }

    public function handle(): void
    {
        usort($this->transactionCollection, function (TransactionDto $a, TransactionDto $b) {
            return $a->getDate() <=> $b->getDate();
        });

        $transactionContext = new TransactionContext();

        array_walk(
            $this->transactionCollection,
            function (TransactionDto $transactionDto) use ($transactionContext) {
                return $this->transactionProcessor->process($transactionDto, $transactionContext);
            }
        );
    }

    public function getOriginalTransactionOrder(): array
    {
        return $this->transactionOriginalOrder;
    }
}
