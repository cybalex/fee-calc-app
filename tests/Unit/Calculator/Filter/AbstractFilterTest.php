<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Calculator\Filter;

use FeeCalcApp\Calculator\Filter\AbstractFilter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AbstractFilterTest extends TestCase
{
    /**
     * @dataProvider evaluationDataProvider
     */
    public function testEvaluate($value1, $value2, $comparison, $expectation): void
    {
        $filter = $this->getMockForAbstractClass(AbstractFilter::class, [],  '', false);

        $this->assertEquals($expectation, $filter->evaluate($value1, $value2, $comparison));
    }

    public function evaluationDataProvider(): \Generator
    {
        yield [1, 3, '<', true];
        yield [1, 1, '<', false];
        yield ['deposit', 'deposit', '!=', false];
        yield ['deposit', 'deposit', '==', true];
        yield [1, 1, '==', true];
        yield [1, 1, '>=', true];
        yield [2, 1, '>=', true];
        yield [1, 1, '<=', true];
        yield [1, 2, '<=', true];
        yield [1, 2, '>', false];
        yield [1, 1, '>', false];
        yield [1, 0, '>', true];
    }

    public function testEvaluateThrowsException(): void
    {
        $filter = $this->getMockForAbstractClass(AbstractFilter::class, [],  '', false);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter comparison ~ is not supported');
        $filter->evaluate(1, 3, '~');
    }
}
