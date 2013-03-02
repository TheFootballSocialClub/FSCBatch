<?php

namespace FSC\Batch\Event;

use FSC\Batch\Batch;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ContextEvent extends Event
{
    /**
     * @var mixed
     */
    private $context;

    public function __construct(Batch $batch, $contexts)
    {
        parent::__construct($batch);

        $this->context = $contexts;
    }

    public function getContext()
    {
        return $this->context;
    }
}
