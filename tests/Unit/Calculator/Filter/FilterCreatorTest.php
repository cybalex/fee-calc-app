<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Filter;

use FeeCalcApp\Calculator\DecisionMaker\DecisionMakerFactory;
use FeeCalcApp\Calculator\Filter\FilterCreator;
use FeeCalcApp\Service\TransactionHistoryManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FilterCreatorTest extends TestCase
{
    public function testGetExceptionWithInvalidFilterConfig(): void
    {
        $transactionHistoryManager = $this->createMock(TransactionHistoryManager::class);
        $decisionMakerFactory = $this->createMock(DecisionMakerFactory::class);

        $filterCreator = new FilterCreator($transactionHistoryManager, $decisionMakerFactory);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config parameter "fee_rate" was provided in requirements section of fee calculators config');
        $filterCreator->getFilterInstance('fee_rate', [0.003]);
    }
}
