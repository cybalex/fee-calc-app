<?php

declare(strict_types=1);

namespace FeeCalcApp\Functional\Command;

use Exception;
use FeeCalcApp\Command\CalculateFeeCommand;
use FeeCalcApp\Service\TransactionHandler;
use FeeCalcApp\Traits\ContainerAware;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateFeeCommandTest extends TestCase
{
    use ContainerAware;

    public function testExecuteWithoutInputFile(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')
            ->with("The required \"--file\" parameter was not provided when running the \"fee.calculate\" command");

        $this->replaceService(LoggerInterface::class, $logger);
        $container = $this->getContainer();

        /** @var CalculateFeeCommand $command */
        $command = $container->get(CalculateFeeCommand::class);
        $questionHelper = $this->createMock(QuestionHelper::class);
        $questionHelper->expects($this->once())->method('ask');
        $questionHelper->expects($this->once())->method('getName')->with()->willReturn('question');
        $command->setHelperSet(new HelperSet([$questionHelper]));
        $commandTester = new CommandTester($command);

        $this->assertEquals(
            1,
            $commandTester->execute([])
        );

        $this->assertEquals(
            "The csv file path was a required parameter for the program to run. Exiting...\n",
            $commandTester->getDisplay()
        );
    }

    public function testExecuteCommandWithPromptedParameter(): void
    {
        /** @var CalculateFeeCommand $command */
        $command = $this->getContainer()->get(CalculateFeeCommand::class);
        $questionHelper = $this->createMock(QuestionHelper::class);
        $questionHelper->expects($this->once())->method('ask')->willReturn('./tests/Functional/Command/input_test.txt');
        $questionHelper->expects($this->once())->method('getName')->with()->willReturn('question');
        $command->setHelperSet(new HelperSet([$questionHelper]));
        $commandTester = new CommandTester($command);

        $this->assertEquals(
            0,
            $commandTester->execute([])
        );

        $this->assertEquals($this->getSuccessOutput(), $commandTester->getDisplay());
    }

    public function testExecuteCommand(): void
    {
        $command = $this->getContainer()->get(CalculateFeeCommand::class);

        $commandTester = new CommandTester($command);
        $this->assertEquals(
            0,
            $commandTester->execute(['--file' => './tests/Functional/Command/input_test.txt'])
        );

        $this->assertEquals($this->getSuccessOutput(), $commandTester->getDisplay());
    }

    public function testExecuteCommandThrowsException(): void
    {
        $handler = $this->createMock(TransactionHandler::class);
        $handler->expects($this->once())->method('handle')->with()
            ->willThrowException(new Exception('Simulated exception'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('critical')
            ->with($this->callback(function(string $actualValue) {
                $this->assertEquals(0, strpos($actualValue, 'Simulated exception thrown in '));

                return true;
            }));

        $container = $this
            ->replaceService(TransactionHandler::class, $handler)
            ->replaceService(LoggerInterface::class, $logger)
            ->getContainer();

        $command = $container->get(CalculateFeeCommand::class);

        $commandTester = new CommandTester($command);
        $this->assertEquals(
            1,
            $commandTester->execute(['--file' => './tests/Functional/Command/input_test.txt'])
        );
    }

    public function testExecuteCommandWithViolations(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->exactly(12))->method('warning')
            ->withConsecutive(
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                         $this->assertLogContainsMessage($actualLogData, 'Wrong format of date time was provided');
                            return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'Amount in wrong format was provided');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'Unsupported value of currency code was provided. Supported values are EUR, USD, JPY');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'Unsupported value of client type was provided. Supported values are private, business');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'Unsupported value of operation type was provided. Supported values are withdraw, deposit');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'Wrong format of date time was provided');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'This value should not be null.');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'This value should not be null.');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'This value should not be null.');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'This value should not be null.');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'Wrong format of date time was provided');
                        return true;
                    })
                ],
                [
                    'Failed to process transaction data',
                    $this->callback(function($actualLogData) {
                        $this->assertLogContainsMessage($actualLogData, 'Invalid value of userId field was provided');
                        return true;
                    })
                ],
            );

        $container = $this->replaceService(LoggerInterface::class, $logger)->getContainer();
        $command = $container->get(CalculateFeeCommand::class);
        $commandTester = new CommandTester($command);

        $this->assertEquals(0, $commandTester->execute(['--file' => './tests/Functional/Command/input_test_violated.txt']));

        $this->assertEquals(<<<TEXT
0.00
0.00
0.30
0.00
0.00

TEXT
            , $commandTester->getDisplay());
    }

    private function assertLogContainsMessage(array $logData, string $message): bool
    {
        $this->assertEquals($message, $logData['message']);

        return true;
    }

    private function getSuccessOutput(): string
    {
        return <<<TEXT
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

TEXT;
    }
}
