<?php

require_once __DIR__.'/../vendor/autoload.php';

use FSC\Batch\JobProcessor;
use FSC\Batch\JobProvider\ArrayJobProvider;
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();

$hashes = array();

$jobProvider = new ArrayJobProvider(range(1, 100));
$jobExecutor = function ($context) use (&$hashes) {
    $hashes[] = crypt($context, '$2a$10$');
};
$jobProcessor = new JobProcessor($jobProvider, $jobExecutor);
$jobProcessor->setOutput($output);

$jobProcessor->run(10);
