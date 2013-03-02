<?php

require_once __DIR__.'/../vendor/autoload.php';

use FSC\Batch\Batch;
use FSC\Batch\Event\ContextEvent;
use FSC\Batch\EventListener\ProgressEventListener;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Component\Console\Output\ConsoleOutput;

$passwords = range(1, 100);
$hashes = array();

$batch = new Batch(new ArrayAdapter($passwords), function (ContextEvent $event) use (&$hashes) {
    $hashes[] = crypt($event->getContext(), '$2a$10$');
});
$batch->getEventDispatcher()->addSubscriber(new ProgressEventListener(new ConsoleOutput()));

$batch->run(10);
