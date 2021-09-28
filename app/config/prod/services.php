<?php

use FeeCalcApp\Calculator\CalculatorDecorator;
use FeeCalcApp\Calculator\Config\ConfigBuilder;
use FeeCalcApp\Calculator\Config\FilterProvider;
use FeeCalcApp\Calculator\Config\Params\ParametersFactory;
use FeeCalcApp\Calculator\DecisionMaker\DecisionMakerFactory;
use FeeCalcApp\Calculator\Fee\SimpleCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCalculator;
use FeeCalcApp\Calculator\Fee\WithdrawalPrivateCustomCurrencyCalculator;
use FeeCalcApp\Calculator\Filter\FilterCreator;
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
use FeeCalcApp\Service\Validation\ValidatorFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return function(ContainerConfigurator $configurator) {

    $configurator->parameters()
        ->set('supported_operation_types', [
            'withdraw',
            'deposit'
        ])
        ->set('supported_client_types', [
            'private',
            'business',
        ])
        ->set('currency_default_code', 'EUR')
        ->set('supported_currency_codes', [
            'EUR',
            'USD',
            'JPY',
        ])
        ->set('currency_default_scale', 2)
        ->set('currency_scale_map', ['JPY' => 0])
        ->set('date_format', 'Y-m-d')
        ->set('logs_date_format', 'Y-m-d H:i:s')
        ->set('currency_api_url', 'http://api.currencylayer.com/live')
        ->set('currency_api_key', '13cd8431d835173a67e1a98c6cbdd6d0')
        ->set('deposit_fee_rate', 0.0003)
        ->set('private_withdrawal_free_weekly_amount', 100000)
        ->set('private_withdrawal_max_weekly_discounts_number', 3)
        ->set('withdrawal_private_fee_rate', 0.003)
        ->set('withdrawal_business_fee_rate', 0.005)
        ->set('log_file', './var/log/logs.txt')

        ->set('fee_calculation_config', [
            'deposit_calculator' => [
                'calculator' => SimpleCalculator::class,
                'enabled' => true,
                'params' => [
                    'fee_rate' => param('deposit_fee_rate'),
                ],
                'requirements' => [
                    'operation_type' => 'deposit',
                ]
            ],
            'withdrawal_business_calculator' => [
                'calculator' => SimpleCalculator::class,
                'enabled' => true,
                'params' => [
                    'fee_rate' => param('withdrawal_business_fee_rate'),
                ],
                'requirements' => [
                    'client_type' => 'business',
                    'operation_type' => 'withdraw',
                ]
            ],
            'withdrawal_private_no_discount_calculator' => [
                'calculator' => SimpleCalculator::class,
                'enabled' => true,
                'params' => [
                    'fee_rate' => param('withdrawal_private_fee_rate'),
                ],
                'requirements' => [
                    'operation_type' => 'withdraw',
                    'client_type' => 'private',
                    'weekly_transactions' => ['>=', param('private_withdrawal_max_weekly_discounts_number')],
                    'currency_code' => param('currency_default_code')
                ],
            ],
            'withdrawal_private_calculator' => [
                'calculator' => WithdrawalPrivateCalculator::class,
                'enabled' => true,
                'extends' => 'withdrawal_private_no_discount_calculator',
                'params' => [
                    'free_weekly_transaction_amount' => param('private_withdrawal_free_weekly_amount'),
                ],
                'requirements' => [
                    'weekly_transactions' => ['<', param('private_withdrawal_max_weekly_discounts_number')],
                ]
            ],
            'withdrawal_private_custom_currency_calculator' => [
                'calculator' => WithdrawalPrivateCustomCurrencyCalculator::class,
                'enabled' => true,
                'extends' => 'withdrawal_private_calculator',
                'requirements' => [
                    'currency_code' => ['!=', param('currency_default_code')],
                ]
            ]
        ])
    ;

    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->set(TransactionStorageInterface::class, TransactionStorageInterface::class)
        ->class(InMemoryTransactionStorage::class);

    $services->set(FileReaderInterface::class, FileReaderInterface::class)
        ->class(CsvFileReader::class)
        ->args([
            service(FileInfoInterface::class)
        ]);

    $services->set(FileInfoInterface::class, FileInfoInterface::class)
        ->class(FileInfo::class);

    $services->set(ClockInterface::class, ClockInterface::class)
        ->class(Clock::class);

    $services->set(Math::class, Math::class)
        ->args([param('currency_default_scale')])
        ->public()
        ->share();

    $services->set(LogFormatterInterface::class, LogFormatterInterface::class)
        ->class(PlainTextLogFormatter::class)
        ->arg(0, param('logs_date_format'));

    $services->set(LoggerInterface::class, LoggerInterface::class)
        ->class(FileLogger::class)
        ->args([
            service(LogFormatterInterface::class),
            param('log_file'),
            service(ClockInterface::class),
            service(FileInfoInterface::class)
        ]);

    $services->set(ExchangeRateClientInterface::class, ExchangeRateClientInterface::class)
        ->class(ExchangeRateHttpClient::class)
        ->args([
            param('currency_api_url'),
            param('currency_api_key'),
            service(CurrencyConfig::class),
            service(LoggerInterface::class)
        ]);

    $services
        ->load('FeeCalcApp\\Calculator\\Fee\\', '../src/Calculator/Fee')
        ->tag('fee_calculator');

    $services
        ->set(ConfigBuilder::class, ConfigBuilder::class)
        ->arg(0, param('fee_calculation_config'));

    $services
        ->set(CurrencyConfig::class, CurrencyConfig::class)
        ->args([
            param('currency_default_code'),
            param('supported_currency_codes'),
            param('currency_default_scale'),
            param('currency_scale_map'),
        ]);

    $services->set(DatetimeHelper::class, DatetimeHelper::class);

    $services
        ->set(TransactionHistoryManager::class, TransactionHistoryManager::class)
        ->args(
            [
                service(ExchangeRateClientInterface::class),
                service(TransactionStorageInterface::class),
                service(DateTimeHelper::class),
                service(Math::class),
                service(CurrencyConfig::class)
            ]
        );

    $services->set(DecisionMakerFactory::class, DecisionMakerFactory::class);

    $services
        ->set(FilterCreator::class, FilterCreator::class)
        ->args([
            service(TransactionHistoryManager::class),
            service(DecisionMakerFactory::class)
        ]);

    $services
        ->set(FilterProvider::class, FilterProvider::class)
        ->arg(0, service(ConfigBuilder::class))
        ->arg(1, service(FilterCreator::class));

    $services->set(ValidatorFactory::class, ValidatorFactory::class);

    $services
        ->set(ValidatorInterface::class, ValidatorInterface::class)
        ->factory(service(ValidatorFactory::class));

    $services
        ->set(ParametersFactory::class, ParametersFactory::class)
        ->arg(0, service(ValidatorInterface::class))
        ->arg(1, service(LoggerInterface::class));

    $services
        ->set(CalculatorDecorator::class, CalculatorDecorator::class)
        ->arg(0, service(FilterProvider::class))
        ->arg(1, service(ConfigBuilder::class))
        ->arg(2, service(ParametersFactory::class));

    $services
        ->set(FeeCalculatorCollectionFactory::class, FeeCalculatorCollectionFactory::class)
        ->arg(0, tagged_iterator('fee_calculator'))
        ->arg(1, service(ConfigBuilder::class))
        ->arg(2, service(CalculatorDecorator::class));

    $services->set(TransactionRequestMetadata::class, TransactionRequestMetadata::class)
        ->arg(0, param('supported_currency_codes'))
        ->arg(1, param('supported_operation_types'))
        ->arg(2, param('supported_client_types'))
        ->arg(3, param('date_format'));

    $services->set(TransactionMapper::class, TransactionMapper::class)
        ->args([
            param('date_format'),
            service(Math::class),
            service(CurrencyConfig::class)
        ]);

    $services->set(FeeCalculationItem::class, FeeCalculationItem::class)
        ->args([
            service(FeeCalculatorCollectionFactory::class),
            5
        ])
        ->tag('transaction_processor_item');
    $services->set(HistoryManagerItem::class, HistoryManagerItem::class)
        ->args([
            service(TransactionHistoryManager::class),
            10
        ])
        ->tag('transaction_processor_item');


    $services->set(TransactionProcessor::class, TransactionProcessor::class)
        ->args([tagged_iterator('transaction_processor_item')]);

    $services
        ->set(TransactionRequestValidator::class, TransactionRequestValidator::class)
        ->arg(0, service(ValidatorInterface::class))
        ->arg(1, service(TransactionRequestMetadata::class));

    $services
        ->set(TransactionHandler::class, TransactionHandler::class)
        ->arg(0, service(TransactionRequestValidator::class))
        ->arg(1, service(TransactionMapper::class))
        ->arg(2, service(TransactionProcessor::class))
        ->arg(3, service(LoggerInterface::class));

    $services
        ->set(CalculateFeeCommand::class, CalculateFeeCommand::class)
        ->args(
            [
                service(FileReaderInterface::class),
                service(TransactionHandler::class),
                service(TransactionHistoryManager::class),
                service(CurrencyConfig::class),
                service(LoggerInterface::class)
            ]
        )
        ->public();
};
