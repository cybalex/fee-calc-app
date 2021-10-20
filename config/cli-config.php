<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;

require_once "vendor/autoload.php";

$paths = [__DIR__ . "/../src/Model"];
$isDevMode = true;

$dbParams = require_once(__DIR__ . "/db-config.php");

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$entityManager = EntityManager::create($dbParams, $config);

return ConsoleRunner::createHelperSet($entityManager);
