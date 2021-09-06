<?php

declare(strict_types=1);

namespace FeeCalcApp\Functional\Command;

use AppFactory;
use FeeCalcApp\Command\CalculateFeeCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateFeeCommandTest extends TestCase
{
    public function testExecuteCommand(): void
    {
        $appFactory = new AppFactory();
        $app = $appFactory->create('test');
        $container = $app->buildContainer();

        $command = $container->get(CalculateFeeCommand::class);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--file' => 'etc/input.csv']);

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

TEXT
, $commandTester->getDisplay());
    }
}
