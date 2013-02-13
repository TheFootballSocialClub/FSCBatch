<?php

namespace FSC\Batch;

use FSC\Batch\Event\Event;
use FSC\Batch\Event\ExecuteEvent;
use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class Batch
{
    const EVENT_RUN_START = 'run.start';
    const EVENT_BATCH_START = 'batch.start';
    const EVENT_EXECUTE = 'execute';
    const EVENT_BATCH_END = 'batch.end';
    const EVENT_RUN_END = 'run.end';

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var int
     */
    protected $defaultBatchSize;

    /**
     * @var int
     */
    protected $jobsCount;

    /**
     * @var int
     */
    protected $currentJobOffset;

    public function __construct(AdapterInterface $adapter, $callback, $defaultBatchSize = 50)
    {
        $this->adapter = $adapter;
        $this->eventDispatcher = new EventDispatcher();
        $this->defaultBatchSize = $defaultBatchSize;

        $this->eventDispatcher->addListener(static::EVENT_EXECUTE, $callback);
    }

    public function run($batchSize = null)
    {
        $batchSize = $batchSize ?: $this->defaultBatchSize;

        $this->currentJobOffset = 0;
        $this->jobsCount = $this->adapter->getNbResults();

        if (!is_int($this->jobsCount) || 0 > $this->jobsCount) {
            throw new \LogicException(sprintf('The JobProvider getJobsCount() method should return an integer >= 0. (got "%s")', $this->jobsCount));
        }

        $this->eventDispatcher->dispatch(static::EVENT_RUN_START, new Event($this));

        while ($this->currentJobOffset < $this->jobsCount) {
            $this->eventDispatcher->dispatch(static::EVENT_BATCH_START, new Event($this));

            $limit = min($batchSize, $this->getRemainingJobsCount());
            $contexts = $this->adapter->getSlice($this->currentJobOffset, $limit);

            if (is_array($contexts) || $contexts instanceof \Traversable) {
                foreach ($contexts as $context) {
                    $this->eventDispatcher->dispatch(static::EVENT_EXECUTE, new ExecuteEvent($context, $this));

                    $this->currentJobOffset++;
                }
            } else {
                // End the batch.
                $this->currentJobOffset = $this->jobsCount;
            }

            $this->eventDispatcher->dispatch(static::EVENT_BATCH_END, new Event($this));
        }

        // Reset the state
        $this->jobsCount = null;
        $this->currentJobOffset = 0;
        $this->eventDispatcher->dispatch(static::EVENT_RUN_END, new Event($this));
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function getCurrentJobOffset()
    {
        return $this->currentJobOffset;
    }

    public function getDefaultBatchSize()
    {
        return $this->defaultBatchSize;
    }

    public function getJobsCount()
    {
        return $this->jobsCount;
    }

    public function getRemainingJobsCount()
    {
        return $this->jobsCount - $this->currentJobOffset;
    }
}
