<?php

namespace FSC\Batch\Command;

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

        $batch->run($input->getOption('batch-size'), $output);
    }

    /**
     * @return Batch
     */
    abstract protected function createBatch();
}
