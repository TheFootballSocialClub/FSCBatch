<?php

namespace FSC\Batch;

class JobsProcessor
{
    /**
     * @var JobProviderInterface
     */
    protected $jobProvider;

    /**
     * @var JobExecutorInterface
     */
    protected $jobExecutor;

    /**
     * @var int The batch size
     */
    protected $batchSize;

    protected $running;

    protected $jobsCount;
    protected $currentJobOffset;

    public function __construct(JobProviderInterface $jobProvider, JobExecutorInterface $jobProcessor, $batchSize = 50)
    {
        $this->jobProvider = $jobProvider;
        $this->jobExecutor = $jobProcessor;
        $this->batchSize = $batchSize;
        $this->running = false;
    }

    public function run()
    {
        if ($this->running) {
            throw new \RuntimeException('You cannot run the processor while it is still running.');
        }

        $this->running = true;
        $this->currentJobOffset = 0;

        $jobsCount = $this->jobProvider->getJobsCount();

        if (!is_int($jobsCount) || 0 > $jobsCount) {
            throw new \RuntimeException(sprintf('The JobProvider getJobsCount() method should return an integer >= 0. (got "%s")', $jobsCount));
        }

        $this->jobsCount = $jobsCount;

        while ($this->currentJobOffset < $this->jobsCount) {
            $limit = min($this->batchSize, $this->getRemainingJobsCount());
            $contexts = $this->jobProvider->getJobsContexts($this->currentJobOffset, $limit);
            $contextsCount = count($contexts);

            if ($contextsCount > $this->batchSize) {
                throw new \RuntimeException(sprintf('The JobProvider getJobsContexts() method should return less than "%d" contexts.', $this->batchSize));
            }

            if (1 > $contextsCount) {
                throw new \RuntimeException('The JobProvider getJobsContexts() method should return at least one context');
            }

            if ($contextsCount > $this->getRemainingJobsCount()) {
                throw new \RuntimeException(sprintf('The JobProvider getJobsContexts() method returned %d contexts, but there should only have %d left to do.', $contextsCount, $this->jobsCount - $this->currentJobOffset));
            }

            foreach ($contexts as $context) {
                $this->jobExecutor->execute($context);

                $this->currentJobOffset++;
            }

            $this->onContextsExecuted();
        }

        $this->jobsCount = null;
        $this->currentJobOffset = 0;
        $this->running = false;
    }

    protected function onContextsExecuted()
    {

    }

    protected function getRemainingJobsCount()
    {
        return $this->jobsCount - $this->currentJobOffset;
    }

    /**
     * @return JobExecutorInterface
     */
    public function getJobExecutor()
    {
        return $this->jobExecutor;
    }

    /**
     * @return JobProviderInterface
     */
    public function getJobProvider()
    {
        return $this->jobProvider;
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }
}
