<?php

use DI\Container;
use FeeCalcApp\Calculator\CalculatorDecorator;
use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\Config\ConfigBuilderInterface;
use FeeCalcApp\Calculator\Config\FilterProvider;
use FeeCalcApp\Calculator\Config\Params\ParametersFactory;
use FeeCalcApp\Calculator\Fee\SimpleCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCustomCurrencyCalculator;
use FeeCalcApp\Command\CalculateFeeCommand;
use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\Helper\Clock\Clock;
use FeeCalcApp\Helper\Clock\ClockInterface;
use FeeCalcApp\Helper\DatetimeHelper;
use FeeCalcApp\Helper\File\FileInfo;
use FeeCalcApp\Helper\File\FileInfoInterface;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateHttpClient;
use FeeCalcApp\Service\FeeCalculatorCollectionFactory;
use FeeCalcApp\Service\Logger\FileLogger;
use FeeCalcApp\Service\Logger\LogFormatterInterface;
use FeeCalcApp\Service\Logger\PlainTextLogFormatter;
use FeeCalcApp\Service\Math;
use FeeCalcApp\Service\Reader\CsvFileReader;
use FeeCalcApp\Service\Reader\FileReaderInterface;
use FeeCalcApp\Service\Transaction\InMemoryTransactionStorage;
use FeeCalcApp\Service\Transaction\Processor\Item\FeeCalculationItem;
use FeeCalcApp\Service\Transaction\Processor\Item\HistoryManagerItem;
use FeeCalcApp\Service\Transaction\Processor\TransactionProcessor;
use FeeCalcApp\Service\Transaction\TransactionStorageInterface;
use FeeCalcApp\Service\TransactionHandler;
use FeeCalcApp\Service\TransactionHistoryManager;
use FeeCalcApp\Service\TransactionMapper;
use FeeCalcApp\Service\Validation\TransactionRequestMetadata;
use FeeCalcApp\Service\Validation\TransactionRequestValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

return [
        TransactionStorageInterface::class => DI\create(InMemoryTransactionStorage::class),
        FileReaderInterface::class => function (Container $c) {
            return new CsvFileReader($c->get(FileInfoInterface::class));
        },
        ClockInterface::class => DI\create(Clock::class),
        FileInfoInterface::class => DI\create(FileInfo::class),

        Math::class => function (Container $c) {
            return new Math($c->get('currency_default_scale'));
        },
        ExchangeRateClientInterface::class => function(Container $c) {
            return new ExchangeRateHttpClient(
                $c->get('currency_api_url'),
                $c->get('currency_api_key'),
                $c->get(CurrencyConfig::class),
                $c->get(LoggerInterface::class)
            );
        },
        WithdrawalPrivateCalculator::class => function(Container $c) {
            return new WithdrawalPrivateCalculator(
                $c->get(Math::class),
                $c->get(TransactionHistoryManager::class),
                $c->get(CurrencyConfig::class)
            );
        },
        WithdrawalPrivateCustomCurrencyCalculator::class => function(Container $c) {
            return new WithdrawalPrivateCustomCurrencyCalculator(
                $c->get(Math::class),
                $c->get(TransactionHistoryManager::class),
                $c->get(ExchangeRateClientInterface::class),
                $c->get(CurrencyConfig::class)
            );
        },
        FeeCalculatorCollectionFactory::class => function (Container $c) {
            return new FeeCalculatorCollectionFactory(
                $c->get(ConfigBuilderInterface::class),
                $c,
                $c->get(CalculatorDecorator::class)
            );
        },
        SimpleCalculator::class => function (Container $c) {
            return new SimpleCalculator(
                $c->get(Math::class)
            );
        },
        FeeCalculationItem::class => function(Container $c) {
             return new FeeCalculationItem(
                 $c->get(FeeCalculatorCollectionFactory::class),
                 5
             );
        },
        HistoryManagerItem::class => function(Container $c) {
            return new HistoryManagerItem(
                $c->get(TransactionHistoryManager::class),
                10
            );
        },
        TransactionProcessor::class => function (Container $c) {
            return new TransactionProcessor([
                $c->get(HistoryManagerItem::class),
                $c->get(FeeCalculationItem::class),
            ]);
        },
        LogFormatterInterface::class => function (Container $c) {
            return new PlainTextLogFormatter($c->get('logs_date_format'));
        },
        LoggerInterface::class => function(Container $c) {
            return new FileLogger(
                $c->get(LogFormatterInterface::class),
                $c->get('log_file'),
                $c->get(ClockInterface::class),
                $c->get(FileInfoInterface::class)
            );
        },

        CalculateFeeCommand::class => function (Container $c) {
            return new CalculateFeeCommand(
                $c->get(FileReaderInterface::class),
                $c->get(TransactionHandler::class),
                $c->get(TransactionHistoryManager::class),
                $c->get(CurrencyConfig::class),
                $c->get(LoggerInterface::class)
            );
        },

        ValidatorInterface::class => function () {
            $validatorBuilder = new ValidatorBuilder();
            return $validatorBuilder
                ->addMethodMapping('loadValidatorMetadata')
                ->getValidator();
        },

        TransactionRequestMetadata::class => function (Container $c) {
            return new TransactionRequestMetadata(
                $c->get('supported_currency_codes'),
                $c->get('supported_operation_types'),
                $c->get('supported_client_types'),
                $c->get('date_format')
            );
        },

        TransactionMapper::class => function (Container $c) {
            return new TransactionMapper(
                $c->get('date_format'),
                $c->get(Math::class),
                $c->get(CurrencyConfig::class)
            );
        },

        TransactionHandler::class => function (Container $c) {
            return new TransactionHandler(
                $c->get(TransactionRequestValidator::class),
                $c->get(TransactionMapper::class),
                $c->get(TransactionProcessor::class),
                $c->get(LoggerInterface::class)
            );
        },
        PlainTextLogFormatter::class => function (Container $c) {
            return new PlainTextLogFormatter($c->get('logs_date_format'));
        },
        CurrencyConfig::class => function (Container $c) {
            return new CurrencyConfig(
                $c->get('currency_default_code'),
                $c->get('supported_currency_codes'),
                $c->get('currency_default_scale'),
                $c->get('currency_scale_map'),
            );
        },
        TransactionHistoryManager::class => function (Container $c) {
            return new TransactionHistoryManager(
                $c->get(ExchangeRateClientInterface::class),
                $c->get(TransactionStorageInterface::class),
                $c->get(DateTimeHelper::class),
                $c->get(Math::class),
                $c->get(CurrencyConfig::class)
            );
        },
        ConfigBuilderInterface::class => function (Container $c) {
            return new ConfigBuilder($c->get('fee_calculation_config'));
        },
        ParametersFactory::class => function (Container $c) {
            return new ParametersFactory(
                $c->get(ValidatorInterface::class),
                $c->get(LoggerInterface::class)
            );
        },
        CalculatorDecorator::class => function (Container $c) {
            return new CalculatorDecorator(
                $c->get(FilterProvider::class),
                $c->get(ConfigBuilderInterface::class),
                $c->get(ParametersFactory::class)
            );
        },
        FilterProvider::class => function (Container $c) {
          return new FilterProvider(
              $c->get(ConfigBuilderInterface::class)
          );
        },
    ];
