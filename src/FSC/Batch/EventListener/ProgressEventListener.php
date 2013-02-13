<?php

namespace FSC\Batch\EventListener;

use FSC\Batch\Batch;
use FSC\Batch\Event\Event;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ProgressEventListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Batch::EVENT_RUN_START => array('onRunStart', 255),
            Batch::EVENT_BATCH_START => array('onBatchStart', 255),
            Batch::EVENT_BATCH_END => array('onBatchEnd', -255),
            Batch::EVENT_RUN_END => array('onRunEnd', -255),
        );
    }

    /**
     * @var OutputInterface
     */
    protected $output;

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

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function onRunStart(Event $event)
    {
        $this->runStartTime = microtime(true);

        $mem = memory_get_usage(true) / 1000000;

        $this->output->writeln(sprintf('Batch run start. %s jobs [Mem: %.2f MB]',
            $event->getBatch()->getJobsCount(),
            $mem
        ));
    }

    public function onBatchStart(Event $event)
    {
        $this->currentBatchStartTime = microtime(true);
        $this->currentBatchStartDateTime = new \DateTime();
    }

    public function onBatchEnd(Event $event)
    {
        $time = microtime(true);
        $totalTime = $time - $this->runStartTime;
        $delta = $time - $this->currentBatchStartTime;
        $progress = $event->getBatch()->getCurrentJobOffset() / $event->getBatch()->getJobsCount();
        $estimatedTotalTime = $progress == 0 ? 0.0 : $totalTime / $progress;
        $remainingTime = $estimatedTotalTime - $totalTime;

        gc_collect_cycles(); // Make sure the memory get usage is "accurate"
        $mem = memory_get_usage(true) / 1000000;

        $countLength = strlen((string) $event->getBatch()->getJobsCount());
        $this->output->writeln(sprintf('[%'.$countLength.'d/%'.$countLength.'d] [%6.2f %%] ([? %s] - [Elapsed %8s] - [Remaining %8s]) [Mem: %5.2f MB]',
            $event->getBatch()->getCurrentJobOffset(),
            $event->getBatch()->getJobsCount(),
            $progress * 100,
            $this->secondsToString($delta),
            $this->secondsToString($totalTime),
            $this->secondsToString($remainingTime),
            $mem
        ));
    }

    public function onRunEnd(Event $event)
    {
        $totalTime = microtime(true) - $this->currentBatchStartTime;

        $this->output->writeln(sprintf('Batch run end. took %s [Mem: %.2f MB]',
            $this->secondsToString($totalTime),
            memory_get_usage(true) / 1000000
        ));
    }

    /**
     * @return float
     */
    public function getLastBatchDuration()
    {
        return $this->lastBatchDuration;
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
}
