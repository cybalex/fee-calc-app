<?php

use DI\Container;
use FeeCalcApp\Calculator\Fee\DepositCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalBusinessCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateNoDiscountCalculator;
use FeeCalcApp\Command\CalculateFeeCommand;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateCacheProxy;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateClient;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;
use FeeCalcApp\Service\FeeCalculatorCollection;
use FeeCalcApp\Service\HttpClient\HttpClient;
use FeeCalcApp\Service\HttpClient\HttpClientInterface;
use FeeCalcApp\Service\Logger\FileLogger;
use FeeCalcApp\Service\Logger\LogFormatterInterface;
use FeeCalcApp\Service\Logger\PlainTextLogFormatter;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\Reader\CsvFileReader;
use FeeCalcApp\Service\Reader\FileReaderInterface;
use FeeCalcApp\Service\Transaction\InMemoryTransactionStorage;
use FeeCalcApp\Service\Transaction\TransactionStorageInterface;
use FeeCalcApp\Service\TransactionBuilder;
use FeeCalcApp\Service\TransactionHistoryManager;
use FeeCalcApp\Service\TransactionProcessor;
use FeeCalcApp\Service\TransactionProcessorObserver;
use Psr\Log\LoggerInterface;

$parameters = require(__DIR__ . '/parameters.php');

return array_merge(
    $parameters,
    [
        TransactionStorageInterface::class => DI\create(InMemoryTransactionStorage::class),
        HttpClientInterface::class => DI\create(HttpClient::class),
        FileReaderInterface::class => DI\create(CsvFileReader::class),

        Math::class => function (Container $c) {
            return new Math($c->get('math_scale'));
        },

        ExchangeRateClientInterface::class => function(Container $c) {
            $exchangeRateClient = new ExchangeRateClient(
                    $c->get(HttpClientInterface::class),
                    $c->get('currency_api_url'),
                    $c->get('currency_api_key')
                );

            return new ExchangeRateCacheProxy($exchangeRateClient);
        },

        DepositCalculator::class => function (Container $c) {
            return new DepositCalculator($c->get(Math::class), $c->get('deposit_fee_rate'));
        },

        WithdrawalBusinessCalculator::class => function (Container $c) {
            return new WithdrawalBusinessCalculator($c->get(Math::class), $c->get('withdrawal_business_fee_rate'));
        },

        WithdrawalPrivateCalculator::class => function(Container $c) {
            return new WithdrawalPrivateCalculator(
                $c->get(TransactionHistoryManager::class),
                $c->get(ExchangeRateCacheProxy::class),
                $c->get(Math::class),
                $c->get('withdrawal_private_fee_rate'),
                $c->get('default_currency_code'),
                $c->get('private_withdrawal_free_weekly_amount'),
                $c->get('private_withdrawal_max_weekly_discounts_number')
            );
        },
        WithdrawalPrivateNoDiscountCalculator::class => function (Container $c) {
            return new WithdrawalPrivateNoDiscountCalculator(
                $c->get(Math::class),
                $c->get(TransactionHistoryManager::class),
                $c->get('withdrawal_private_fee_rate')
            );
        },
        FeeCalculatorCollection::class => function (Container $c) {
            return (new FeeCalculatorCollection())
                ->add($c->get(DepositCalculator::class))
                ->add($c->get(WithdrawalBusinessCalculator::class))
                ->add($c->get(WithdrawalPrivateCalculator::class))
                ->add($c->get(WithdrawalPrivateNoDiscountCalculator::class));
        },
        TransactionProcessor::class => function (Container $c) {
            $transactionProcessor = new TransactionProcessor(
                $c->get(FeeCalculatorCollection::class)
            );
            $transactionProcessor->attach($c->get(TransactionProcessorObserver::class));

            return $transactionProcessor;
        },

        LogFormatterInterface::class => DI\create(PlainTextLogFormatter::class),

        LoggerInterface::class => function(Container $c) {
            return new FileLogger($c->get(LogFormatterInterface::class, $c->get('log_file')));
        },

        CalculateFeeCommand::class => function (Container $c) {
            $command = new CalculateFeeCommand(
                $c->get(FileReaderInterface::class),
                $c->get(TransactionBuilder::class),
                $c->get(TransactionProcessor::class)
            );
            $command->setLogger($c->get(LoggerInterface::class));

            return $command;
        }
    ]
);
