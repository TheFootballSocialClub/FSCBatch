<?php

namespace FSC\Batch\Event;

use FSC\Batch\Batch;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ExecuteEvent extends Event
{
    /**
     * @var mixed
     */
    private $context;

    public function __construct(Batch $batch, $context)
    {
        parent::__construct($batch);

        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }
}
