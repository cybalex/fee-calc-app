<?php

declare(strict_types=1);

namespace FeeCalcApp\Calculator\Filter;

use FeeCalcApp\Calculator\DecisionMaker\DecisionMakerFactory;
use FeeCalcApp\Service\TransactionHistoryManager;
use InvalidArgumentException;

class FilterCreator
{
    public function __construct(
        private TransactionHistoryManager $transactionHistoryManager,
        private DecisionMakerFactory $decisionMakerFactory
    ) {
    }

    public function getFilterInstance(string $filterName, array $filtersConfig): FilterInterface
    {
        if (count($filtersConfig) === 1) {
            $comparisonOperator = ComparisonInterface::COMPARISON_EQUALS;
        }

        if (is_scalar(end($filtersConfig))) {
            $values = [end($filtersConfig)];
        }

        switch ($filterName) {
            case 'operation_type':
                return new OperationTypeFilter(
                    $values ?? end($filtersConfig),
                    $comparisonOperator ?? reset($filtersConfig),
                    $this->decisionMakerFactory
                );
            case 'weekly_transactions':
                return new WeeklyOperationsNumberFilter(
                    $this->transactionHistoryManager, end($filtersConfig),
                    $comparisonOperator ?? reset($filtersConfig),
                    $this->decisionMakerFactory
                );
            case 'currency_code':
                return new CurrencyFilter(
                    $values ?? end($filtersConfig),
                    $comparisonOperator ?? reset($filtersConfig),
                    $this->decisionMakerFactory
                );
            case 'is_enabled':
                return new IsEnabledFilter(reset($filtersConfig), $this->decisionMakerFactory);
            case 'client_type':
                return new ClientTypeFilter(
                    $values ?? end($filtersConfig),
                    $comparisonOperator ?? reset($filtersConfig),
                    $this->decisionMakerFactory
                );
        }

        throw new InvalidArgumentException(sprintf('Invalid config parameter "%s" was provided in requirements section of fee calculators config', $filterName));
    }
}
