<?php

declare(strict_types=1);

class AppTest extends App
{
    public function getConfigs(): array
    {
        return array_merge(
            [parent::getConfigDir() . 'services.php'],
            parent::getConfigs()
        );
    }

    public function getConfigDir(): string
    {
        return __DIR__ . '/config/test/';
    }
}
