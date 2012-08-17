<?php

require_once __DIR__.'/../vendor/autoload.php';

use FSC\Batch\Batch;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();

$passwords = range(1, 100);
$hashes = array();

$batch = new Batch(new ArrayAdapter($passwords), function ($context) use (&$hashes) {
    $hashes[] = crypt($context, '$2a$10$');
});

$batch->run(10, $output);
