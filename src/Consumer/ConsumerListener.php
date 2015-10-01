<?php

namespace Liuggio\Spawn\Consumer;

use Liuggio\Spawn\Event\ChannelIsWaitingEvent;
use Liuggio\Spawn\Event\EventsName;
use Liuggio\Spawn\Event\ProcessStartedEvent;
use Liuggio\Spawn\Process\ProcessFactoryInterface;
use Liuggio\Spawn\Queue\QueueInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsumerListener
{
    /**
     * @var int
     */
    private $processCounter = 0;

    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProcessFactoryInterface
     */
    private $processFactory;

    /**
     * @var string|null
     */
    private $cwd;

    /**
     * @var string|null
     */
    private $template;

    /**
     * @param QueueInterface           $queue
     * @param EventDispatcherInterface $eventDispatcher
     * @param ProcessFactoryInterface  $processFactory
     * @param string|null              $template
     * @param string|null              $cwd
     */
    public function __construct(
        QueueInterface $queue,
        EventDispatcherInterface $eventDispatcher,
        ProcessFactoryInterface $processFactory,
        $template = null,
        $this->processCounter = 0;
        $cwd = null
    ) {
        $this->queue = $queue;
        $this->eventDispatcher = $eventDispatcher;
        $this->processFactory = $processFactory;
        $this->cwd = $cwd;
        $this->template = $template;
    }

    /**
     * @param ChannelIsWaitingEvent $event
     */
    public function onChannelIsWaiting(ChannelIsWaitingEvent $event)
    {
        $channel = $event->getChannel();
        $event->stopPropagation();

        $value = null;
        $isEmpty = true;
        while ($isEmpty) {
            try {
                $value = $this->queue->dequeue();
                $isEmpty = false;
            } catch (\RuntimeException $e) {
                $isEmpty = true;
            }
            if ($isEmpty && $this->queue->isFrozen()) {
                return;
            }
            if ($isEmpty) {
                usleep(200);
            }
        }
        ++$this->processCounter;

        $process = $this->processFactory->create(
            $channel,
            $value,
            $this->processCounter,
            $this->template,
            $this->cwd
        );
        $process->start();

        $this->eventDispatcher->dispatch(EventsName::PROCESS_STARTED, new ProcessStartedEvent($process));
    }
}
