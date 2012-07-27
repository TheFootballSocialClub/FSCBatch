<?php

namespace FSC\Batch\JobProvider;

class ArrayJobProvider implements JobProviderInterface
{
    protected $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobsCount()
    {
        return count($this->array);
    }

    /**
     * {@inheritdoc}
     */
    public function getJobsContexts($offset, $limit)
    {
        return array_slice($this->array, $offset, $limit);
    }

}
