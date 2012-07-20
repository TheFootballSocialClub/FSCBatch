<?php

$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    throw new RuntimeException('You should run composer before trying to run the tests.');
}

return require_once $autoloadPath;
