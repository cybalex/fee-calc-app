<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface AppInterface
{
    public function buildContainer(array $definitions = []): ContainerBuilder;

    public function getConfigDir(): string;

    public function getConfigs(): array;
}
