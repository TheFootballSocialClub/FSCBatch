<?php

require_once __DIR__.'/../vendor/autoload.php';

use FSC\Batch\Batch;
use FSC\Batch\Event\ExecuteEvent;
use FSC\Batch\EventListener\ProgressEventListener;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();

$passwords = range(1, 100);
$hashes = array();

$batch = new Batch(new ArrayAdapter($passwords), function (ExecuteEvent $event) use (&$hashes) {
    $hashes[] = crypt($event->getContext(), '$2a$10$');
});
$batch->getEventDispatcher()->addSubscriber(new ProgressEventListener($output));

$batch->run(10, $output);
