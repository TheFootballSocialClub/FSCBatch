<?php

namespace FSC\Batch\Event;

use FSC\Batch\Batch;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class Event extends BaseEvent
{
    /**
     * @var Batch
     */
    private $batch;

    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }

    public function getBatch()
    {
        return $this->batch;
    }
}
