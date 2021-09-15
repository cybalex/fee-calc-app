<?php

declare(strict_types=1);

namespace FeeCalcApp\Functional\Command;

use AppFactory;
use Exception;
use FeeCalcApp\Command\CalculateFeeCommand;
use FeeCalcApp\Service\TransactionHandler;
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
        $this->assertEquals(
            0,
            $commandTester->execute(['--file' => './tests/Functional/Command/input_test.txt'])
        );

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

    public function testExecuteCommandThrowsException(): void
    {
        $handler = $this->createMock(TransactionHandler::class);
        $handler->expects($this->once())->method('handle')->with()
            ->willThrowException(new Exception('Simulated exception'));

        $appFactory = new AppFactory();
        $app = $appFactory->create('test');
        $container = $app->buildContainer([TransactionHandler::class => $handler]);

        $command = $container->get(CalculateFeeCommand::class);

        $container->set(TransactionHandler::class, $handler);

        $commandTester = new CommandTester($command);
        $this->assertEquals(
            1,
            $commandTester->execute(['--file' => './tests/Functional/Command/input_test.txt'])
        );
    }
}
