<?php

namespace FSC\Batch\Command;

use FSC\Batch\EventListener\ProgressEventListener;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use FSC\Batch\Batch;

abstract class BatchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->addOption('batch-size', 'bs', InputOption::VALUE_OPTIONAL, 'Batch size.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batch = $this->createBatch();
        $batch->getEventDispatcher()->addSubscriber(new ProgressEventListener($output));

        $batch->run($input->getOption('batch-size'));
    }

    /**
     * @return Batch
     */
    abstract protected function createBatch();
}
