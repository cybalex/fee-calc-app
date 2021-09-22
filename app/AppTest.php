<?php

declare(strict_types=1);

class AppTest extends App
{
    public function getConfigDir(): string
    {
        return __DIR__ . '/config/test/';
    }
}
