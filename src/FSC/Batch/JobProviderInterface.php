<?php

namespace FSC\Batch;

interface JobProviderInterface
{
    /**
     * @return int The total number of jobs
     */
    public function getJobsCount();

    /**
     * @param int $offset The offset at which the provider should start searching for contexts
     * @param int $limit  The max number of contexts to return
     *
     * @return array The jobs contexts
     */
    public function getJobsContexts($offset, $limit);
}
