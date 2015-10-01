<?php

namespace Liuggio\Spawn\UI;

use Liuggio\Spawn\Event\EventsName;
use Liuggio\Spawn\Event\ProcessCompletedEvent;
use Liuggio\Spawn\Event\ProcessGeneratedBufferEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StdOutUISubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            // EventsName::INPUT_LINE_ENQUEUED => ['onInputLineEnqueued', 100],
            // EventsName::INPUT_LINE_DEQUEUED => ['onInputLineDequeued', 100],
            EventsName::QUEUE_IS_FROZEN => ['onFrozenQueue', 100],
            // EventsName::QUEUE_IS_EMPTY => ['onQueueEmptied', 100],
            EventsName::PROCESS_STARTED => ['onProcessStarted', 100],
            EventsName::PROCESS_COMPLETED => ['onProcessCompleted', 100],
            // EventsName::PROCESS_GENERATED_BUFFER => ['onGeneratedBuffer', 100],
        ];
    }

    /**
     * @param string $name
     * @param array  $arguments
     * 
     * @return string
     */
    public function __call($name, array $arguments = [])
    {
        echo ' - called:'.$name.PHP_EOL;
    }

    /**
     * @param ProcessCompletedEvent $event
     * 
     * @return string
     */
    public function onProcessCompleted(ProcessCompletedEvent $event)
    {
        echo sprintf(
            "%s] onProcessCompleted: [%s] on [%s] with %s\n",
            $event->getProcess()->isSuccessful() ? '✅' : '✗',
            $event->getProcess()->getIncrementalNumber(),
            $event->getProcess()->getChannel(),
            $event->getProcess()->getCommandLine()
        );
    }

    /**
     * @param ProcessGeneratedBufferEvent $event
     * 
     * @return string
     */
    public function onGeneratedBuffer(ProcessGeneratedBufferEvent $event)
    {
        $err = trim($event->getProcess()->getIncrementalErrorOutput());
        $out = trim($event->getProcess()->getIncrementalOutput());
        if (empty($err) && empty($out)) {
            return;
        }

        echo sprintf(
            " - buffer: [%s] on [%s] with %s \n  out %s|err %s\n",
            $event->getProcess()->getIncrementalNumber(),
            $event->getProcess()->getChannel(),
            $event->getProcess()->getCommandLine(),
            $out,
            $err
        );
    }
}
