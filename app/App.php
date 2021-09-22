<?php

declare(strict_types=1);

use DI\Container;
use DI\ContainerBuilder;

class App implements AppInterface
{
    public function buildContainer(array $definitions = []): Container
    {
        return (new ContainerBuilder())
            ->addDefinitions($this->getConfigDir() . 'parameters.php')
            ->addDefinitions($this->getConfigDir() . 'fee_calculators_config.php')
            ->addDefinitions($this->getConfigDir() . 'config.php')
            ->addDefinitions($definitions)
            ->build();
    }

    public function getConfigDir(): string
    {
        return __DIR__ . '/config/prod/';
    }
}
