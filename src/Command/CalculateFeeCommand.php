<?php

declare(strict_types=1);

namespace FeeCalcApp\Command;

use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\Service\Reader\FileReaderInterface;
use FeeCalcApp\Service\TransactionHandler;
use FeeCalcApp\Service\TransactionHistoryManager;
use FeeCalcApp\Service\TransactionRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Throwable;

class CalculateFeeCommand extends Command
{
    protected static $defaultName = 'fee.calculate';

    private FileReaderInterface $fileReader;

    private LoggerInterface $logger;

    private TransactionHandler $transactionHandler;
    private TransactionHistoryManager $transactionHistoryManager;
    private CurrencyConfig $currencyConfig;

    public function __construct(
        FileReaderInterface $fileReader,
        TransactionHandler $transactionHandler,
        TransactionHistoryManager $transactionHistoryManager,
        CurrencyConfig $currencyConfig,
        LoggerInterface $logger
    ) {
        parent::__construct(static::$defaultName);
        $this->fileReader = $fileReader;
        $this->transactionHandler = $transactionHandler;
        $this->transactionHistoryManager = $transactionHistoryManager;
        $this->currencyConfig = $currencyConfig;
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('file')) {
            $filePath = $input->getOption('file');
        } else {
            $question = new Question("Enter a path to a CSV file with transactions to parse\n");
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');

            if (null === $filePath = $helper->ask($input, $output, $question)) {
                $output->write("The csv file path was a required parameter for the program to run. Exiting...\n");
                $this->logger->error("The required \"--file\" parameter was not provided when running the \"" . self::$defaultName . "\" command");

                return 1;
            }
        }

        try {
            $transactionsData = $this->fileReader->read($filePath);

            foreach ($transactionsData as $transactionData) {
                $transactionRequest = new TransactionRequest();
                $transactionRequest
                    ->setUserId($transactionData[1])
                    ->setClientType($transactionData[2])
                    ->setDate($transactionData[0])
                    ->setOperationType($transactionData[3])
                    ->setCurrencyCode($transactionData[5])
                    ->setAmount($transactionData[4]);

                $this->transactionHandler->addTransaction($transactionRequest);
            }

            $this->transactionHandler->handle();

            foreach ($this->transactionHandler->getOriginalTransactionOrder() as $transactionKey) {
                $processedTransaction = $this->transactionHistoryManager->get($transactionKey);
                $scale = $this->currencyConfig->getCurrencyScale($processedTransaction->getCurrencyCode());
                $fee = (float) $processedTransaction->getFee() / (pow(10, $scale));
                $output->write(number_format($fee, $scale, '.', ''), true);
            }
        } catch (Throwable $e) {
            $this->logger->critical(
                $e->getMessage().' thrown in '.$e->getFile().' on line '.$e->getLine()
            );

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
