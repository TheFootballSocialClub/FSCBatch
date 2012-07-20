<?php

namespace FSC\Batch;

interface JobExecutorInterface
{
    /**
     * @param array $context The job context
     */
    public function execute($context);
}
