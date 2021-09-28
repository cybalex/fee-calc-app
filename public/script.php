<?php

require_once('./vendor/autoload.php');

use FeeCalcApp\Command\CalculateFeeCommand;
use Symfony\Component\Console\Application;

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('Missing input file with transaction information');
}

$env = isset($argv[2]) && $argv[2] === 'test' ? 'test' : 'prod';
$container = (new AppFactory())->create($env)->buildContainer();
$container->compile();

$application = new Application();
$application->add($container->get(CalculateFeeCommand::class));
$application->run();
