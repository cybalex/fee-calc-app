<?php

declare(strict_types=1);

namespace FeeCalcApp\Traits;

use AppFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait ContainerAware
{
    protected array $replacedServices = [];

    protected function replaceService(string $alias, object $service): self
    {
        $this->replacedServices[$alias] = $service;

        return $this;
    }

    protected function getContainer(string $env = 'test', array $definitions = []): ContainerBuilder
    {
        $appFactory = new AppFactory();
        $app = $appFactory->create($env);
        $container = $app->buildContainer(array_merge($this->replacedServices, $definitions));
        $this->replacedServices = [];
        $container->compile();

        return $container;
    }
}
