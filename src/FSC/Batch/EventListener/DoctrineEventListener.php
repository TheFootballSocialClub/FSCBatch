<?php

namespace FSC\Batch\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use FSC\Batch\Batch;
use FSC\Batch\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class DoctrineEventListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Batch::EVENT_RUN_START => 'onRunStart',
            Batch::EVENT_BATCH_START => 'onBatchStart',
            Batch::EVENT_BATCH_END => 'onBatchEnd',
            Batch::EVENT_RUN_END => 'onRunEnd',
        );
    }

    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function onBatchEnd(Event $event)
    {
        $this->objectManager->flush();
        $this->objectManager->clear(); // This is a bit hard, but it's the safest memory related
    }
}
