<?php

namespace FSC\Batch\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Symfony\Component\Console\Output\OutputInterface;

class Event extends BaseEvent
{
    /**
     * @var OutputInterface|null
     */
    private $output;

    public function __construct(OutputInterface $output = null)
    {
        $this->output = $output;
    }

    public function getOutput()
    {
        return $this->output;
    }
}
