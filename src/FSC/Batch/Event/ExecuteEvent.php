<?php

namespace FSC\Batch\Event;

use Symfony\Component\Console\Output\OutputInterface;

class ExecuteEvent extends Event
{
    /**
     * @var mixed
     */
    private $context;

    public function __construct($context, OutputInterface $output = null)
    {
        $this->context = $context;

        parent::__construct($output);
    }

    public function getContext()
    {
        return $this->context;
    }
}
