<?php

namespace FSC\Batch;

use Symfony\Component\Console\Output\OutputInterface;

use Pagerfanta\Adapter\AdapterInterface;

class Batch
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var int
     */
    protected $defaultBatchSize;

    protected $jobsCount;
    protected $currentJobOffset;

    /**
     * @var float
     */
    protected $runStartTime;

    /**
     * @var float
     */
    protected $currentBatchStartTime;

    /**
     * @var \DateTime
     */
    protected $currentBatchStartDateTime;

    /**
     * @var float The time the last batch took
     */
    protected $lastBatchDuration;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(AdapterInterface $adapter, $callback, $defaultBatchSize = 50)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('The callback should be a php callable.');
        }

        $this->adapter = $adapter;
        $this->callback = $callback;
        $this->defaultBatchSize = $defaultBatchSize;
    }

    public function run($batchSize = null)
    {
        $this->onRunStart();

        if (null === $batchSize) {
            $batchSize = $this->defaultBatchSize;
        }

        while ($this->currentJobOffset < $this->jobsCount) {
            $this->onBatchStart();

            $limit = min($batchSize, $this->getRemainingJobsCount());
            $contexts = $this->adapter->getSlice($this->currentJobOffset, $limit);

            foreach ($contexts as $context) {
                call_user_func($this->callback, $context);

                $this->currentJobOffset++;
            }

            $this->onBatchEnd();
        }

        $this->onRunEnd();

        $this->jobsCount = null;
        $this->currentJobOffset = 0;
    }

    protected function onRunStart()
    {
        $this->currentJobOffset = 0;
        $this->runStartTime = microtime(true);

        $jobsCount = $this->adapter->getNbResults();

        if (!is_int($jobsCount) || 0 > $jobsCount) {
            throw new \RuntimeException(sprintf('The JobProvider getJobsCount() method should return an integer >= 0. (got "%s")', $jobsCount));
        }

        $this->jobsCount = $jobsCount;

        if (null !== $this->output) {
            $mem = memory_get_usage(true) / 1000000;

            $this->output->writeln(sprintf('Batch run start. %s jobs [Mem: %.2f MB]',
                $this->jobsCount,
                $mem
            ));
        }
    }

    protected function onBatchStart()
    {
        $this->currentBatchStartTime = microtime(true);
        $this->currentBatchStartDateTime = new \DateTime();
    }

    protected function onBatchEnd()
    {
        if (null !== $this->output) {
            $time = microtime(true);
            $totalTime = $time - $this->runStartTime;
            $delta = $time - $this->currentBatchStartTime;
            $progress = $this->currentJobOffset / $this->jobsCount;
            $estimatedTotalTime = $progress == 0 ? 0.0 : $totalTime / $progress;
            $remainingTime = $estimatedTotalTime - $totalTime;

            gc_collect_cycles(); // Make sure the memory get usage is "accurate"
            $mem = memory_get_usage(true) / 1000000;

            $countLength = strlen((string) $this->jobsCount);
            $this->output->writeln(sprintf('[%'.$countLength.'d/%'.$countLength.'d] [%6.2f %%] ([Δ %s] - [Elapsed %8s] - [Remaining %8s]) [Mem: %5.2f MB]',
                $this->currentJobOffset,
                $this->jobsCount,
                $progress * 100,
                $this->secondsToString($delta),
                $this->secondsToString($totalTime),
                $this->secondsToString($remainingTime),
                $mem
            ));
        }
    }

    protected function onRunEnd()
    {
        if (null !== $this->output) {
            $totalTime = microtime(true) - $this->currentBatchStartTime;

            $this->output->writeln(sprintf('Batch run end. took %s [Mem: %.2f MB]',
                $this->secondsToString($totalTime),
                memory_get_usage(true) / 1000000
            ));
        }
    }

    protected function getRemainingJobsCount()
    {
        return $this->jobsCount - $this->currentJobOffset;
    }

    /**
     * Helper, to transform for example "93" to "1 mn, 33s"
     *
     * @param int $seconds
     *
     * @return string
     */
    protected function secondsToString($seconds)
    {
        $units = array(
            'week' => 7 * 24 * 3600,
            'day' => 24 * 3600,
            'hour' => 3600,
            'min' => 60,
            'sec' => 1,
        );

        // specifically handle zero
        if ($seconds == 0) {
            return '0 sec';
        } elseif ($seconds < 1) {
            return sprintf('%.2f sec', $seconds);
        }

        $str = '';

        foreach ($units as $name => $divisor) {
            if ($quot = intval($seconds / $divisor)) {
                $str .= $quot . ' ' . $name;
                $str .= (abs($quot) > 1 ? 's' : '') . ', ';
                $seconds -= $quot * $divisor;
            }
        }

        return substr($str, 0, -2);
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return float
     */
    public function getLastBatchDuration()
    {
        return $this->lastBatchDuration;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }
}
