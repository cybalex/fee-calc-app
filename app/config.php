<?php

use DI\Container;
use FeeCalcApp\Calculator\CalculatorCompiler;
use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\Config\ConfigBuilderInterface;
use FeeCalcApp\Calculator\Config\FilterProvider;
use FeeCalcApp\Calculator\Fee\DepositCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalBusinessCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCustomCurrencyCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateNoDiscountCalculator;
use FeeCalcApp\Calculator\Filter\FilterCreator;
use FeeCalcApp\Command\CalculateFeeCommand;
use FeeCalcApp\Config\CurrencyConfig;
use FeeCalcApp\Helper\DatetimeHelper;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateClientInterface;
use FeeCalcApp\Service\ExchangeRate\ExchangeRateHttpClient;
use FeeCalcApp\Service\FeeCalculatorCollection;
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

$parameters = require(__DIR__ . '/parameters.php');
$feeCalculatorConfig = require(__DIR__ . '/fee_calculators_config.php');

return array_merge(
    $parameters,
    $feeCalculatorConfig,
    [
        TransactionStorageInterface::class => DI\create(InMemoryTransactionStorage::class),
        FileReaderInterface::class => DI\create(CsvFileReader::class),

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

        DepositCalculator::class => function (Container $c) {
            $calculator = new DepositCalculator($c->get(Math::class), $c->get('deposit_fee_rate'));

            return $c->get(CalculatorCompiler::class)->compileFilters($calculator);
        },

        WithdrawalBusinessCalculator::class => function (Container $c) {
            $calculator = new WithdrawalBusinessCalculator($c->get(Math::class), $c->get('withdrawal_business_fee_rate'));

            return $c->get(CalculatorCompiler::class)->compileFilters($calculator);
        },

        WithdrawalPrivateCalculator::class => function(Container $c) {
            $calculator = new WithdrawalPrivateCalculator(
                $c->get(Math::class),
                $c->get(TransactionHistoryManager::class),
                $c->get('withdrawal_private_fee_rate'),
                $c->get('private_withdrawal_max_weekly_discounts_number'),
                $c->get(CurrencyConfig::class),
                $c->get('private_withdrawal_free_weekly_amount'),
            );

            return $c->get(CalculatorCompiler::class)->compileFilters($calculator);
        },
        WithdrawalPrivateCustomCurrencyCalculator::class => function(Container $c) {
            $calculator =  new WithdrawalPrivateCustomCurrencyCalculator(
                $c->get(Math::class),
                $c->get(TransactionHistoryManager::class),
                $c->get('withdrawal_private_fee_rate'),
                $c->get('private_withdrawal_max_weekly_discounts_number'),
                $c->get(ExchangeRateClientInterface::class),
                $c->get(CurrencyConfig::class),
                $c->get('private_withdrawal_free_weekly_amount'),
            );

            return $c->get(CalculatorCompiler::class)->compileFilters($calculator);
        },
        WithdrawalPrivateNoDiscountCalculator::class => function (Container $c) {
            $calculator =  new WithdrawalPrivateNoDiscountCalculator(
                $c->get(Math::class),
                $c->get(TransactionHistoryManager::class),
                $c->get('withdrawal_private_fee_rate'),
                $c->get('private_withdrawal_max_weekly_discounts_number')
            );

            return $c->get(CalculatorCompiler::class)->compileFilters($calculator);
        },
        FeeCalculatorCollection::class => function (Container $c) {
            return (new FeeCalculatorCollection())
                ->add($c->get(DepositCalculator::class))
                ->add($c->get(WithdrawalBusinessCalculator::class))
                ->add($c->get(WithdrawalPrivateCalculator::class))
                ->add($c->get(WithdrawalPrivateNoDiscountCalculator::class))
                ->add($c->get(WithdrawalPrivateCustomCurrencyCalculator::class));
        },

        FeeCalculationItem::class => function(Container $c) {
             return new FeeCalculationItem(
                 $c->get(FeeCalculatorCollection::class),
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
            return new FileLogger($c->get(LogFormatterInterface::class), $c->get('log_file'));
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
        ConfigBuilder::class => function (Container $c) {
            return new ConfigBuilder($c->get('fee_calculation_config'));
        },
        CalculatorCompiler::class => function (Container $c) {
            return new CalculatorCompiler($c->get(FilterProvider::class), $c->get(ConfigBuilder::class));
        },
        FilterProvider::class => function (Container $c) {
          return new FilterProvider(
              $c->get(ConfigBuilderInterface::class),
              $c->get(FilterCreator::class)
          );
        },
        ConfigBuilderInterface::class => function (Container $c) {
            return new ConfigBuilder($c->get('fee_calculation_config'));
        },
    ]
);
