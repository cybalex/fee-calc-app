<?php

declare(strict_types=1);

namespace FeeCalcApp\Command;

use FeeCalcApp\Exception\TransactionException;
use FeeCalcApp\Service\Reader\FileReaderInterface;
use FeeCalcApp\Service\TransactionBuilder;
use FeeCalcApp\Service\TransactionProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CalculateFeeCommand extends Command
{
    protected static $defaultName = 'fee.calculate';

    private FileReaderInterface $fileReader;

    private TransactionBuilder $transactionBuilder;

    private TransactionProcessor $transactionProcessor;

    private LoggerInterface $logger;

    public function __construct(
        FileReaderInterface $fileReader,
        TransactionBuilder $transactionBuilder,
        TransactionProcessor $transactionProcessor
    ) {
        parent::__construct(static::$defaultName);
        $this->fileReader = $fileReader;
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionProcessor = $transactionProcessor;
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getOption('file');

        try {
            $transactionsData = $this->fileReader->read($filePath);

            foreach ($transactionsData as $transactionData) {
                $transaction = $this->transactionBuilder
                    ->setUserId((int) $transactionData[1])
                    ->setClientType($transactionData[2])
                    ->setDate($transactionData[0])
                    ->setOperationType($transactionData[3])
                    ->setCurrencyAmount($transactionData[4], $transactionData[5])
                    ->build();

                $processedTransaction = $this->transactionProcessor->process($transaction);

                $scale = $processedTransaction->getCurrency()->getScale();
                $fee = (float) $processedTransaction->getFee() / (pow(10, $scale));
                $output->write(number_format($fee, $scale, '.', ''), true);
            }
        } catch (TransactionException $e) {
            $this->logger->warning($e->getMessage());
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            return 1;
        }

        return 0;
    }

    protected function configure()
    {
        $this
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to file'
            );
    }
}
