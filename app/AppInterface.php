<?php

use DI\Container;

interface AppInterface
{
    public function buildContainer(): Container;
}
