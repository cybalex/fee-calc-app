<?php

declare(strict_types=1);

namespace FeeCalcApp\Unit\Service\Config;

use FeeCalcApp\Config\CurrencyConfig;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CurrencyConfigTest extends TestCase
{
    public function testGetCurrencyScale(): void
    {
        $currencyConfig = new CurrencyConfig(
            'EUR',
            ['EUR', 'USD', 'JPY'],
            2,
            ['JPY' => 0]
        );

        $this->assertEquals(0, $currencyConfig->getCurrencyScale('JPY'));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency CHF is not supported');
        $currencyConfig->getCurrencyScale('CHF');
    }
}
