<?php

declare(strict_types=1);

namespace FeeCalcApp\Functional;

use PHPUnit\Framework\TestCase;

class BasicScenariosTest extends TestCase
{
    private const RESULT_FILE = './tmp.txt';
    private const INPUT_FILE = './etc/input.csv';

    public function testFeeCalculationScenarios(): void
    {
        @unlink(self::RESULT_FILE);
        exec("php ./public/script.php " . self::INPUT_FILE . " test >> " . self::RESULT_FILE);

        $this->assertEquals(<<<TEXT
0.60
3.00
0.00
0.06
1.50
0
0.70
0.30
0.30
3.00
0.00
0.00
8612

TEXT,
            file_get_contents(self::RESULT_FILE)
);
    }

    protected function tearDown()
    {
        parent::tearDown();
        unlink(self::RESULT_FILE);
    }
}
