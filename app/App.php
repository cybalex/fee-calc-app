<?php

declare(strict_types=1);

use DI\Container;
use DI\ContainerBuilder;

class App implements AppInterface
{
    public function buildContainer(): Container
    {
        return (new ContainerBuilder())
            ->addDefinitions($this->getConfig())
            ->build();
    }

    protected function getConfig(): string
    {
        return __DIR__ . '/config.php';
    }
}