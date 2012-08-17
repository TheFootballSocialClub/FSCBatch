<?php

namespace FSC\Batch;

use Pagerfanta\Adapter\AdapterInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DoctrineBatch extends Batch
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager, AdapterInterface $adapter, $callback, $defaultBatchSize = 50)
    {
        $this->objectManager = $objectManager;

        parent::__construct($adapter, $callback, $defaultBatchSize);
    }

    public function onBatchEnd()
    {
        $this->objectManager->flush();
        $this->objectManager->clear(); // This is a bit hard, but it's the safest memory related

        parent::onBatchEnd();
    }
}
