<?php

namespace FSC\Batch\Event;

use FSC\Batch\Batch;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ContextsEvent extends Event
{
    /**
     * @var mixed
     */
    private $contexts;

    public function __construct(Batch $batch, $contexts)
    {
        parent::__construct($batch);

        $this->contexts = $contexts;
    }

    public function getContexts()
    {
        return $this->contexts;
    }
}
