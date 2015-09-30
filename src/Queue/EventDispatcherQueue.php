<?php

namespace Liuggio\Spawn\Queue;

use Liuggio\Spawn\Event\FrozenQueueEvent;
use Liuggio\Spawn\Event\InputLineDequeuedEvent;
use Liuggio\Spawn\Event\InputLineEnqueuedEvent;
use Liuggio\Spawn\Event\EventsName;
use Liuggio\Spawn\Event\EmptiedQueueEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcherQueue extends SplQueue implements QueueInterface
{
    /** @var  EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * EventDispatcherQueue constructor.
     *
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param null|array                    $array
     */
    public function __construct(EventDispatcherInterface $eventDispatcher = null, $array = null)
    {
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
        parent::__construct($array);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue($value)
    {
        parent::enqueue($value);
        $this->eventDispatcher->dispatch(EventsName::INPUT_LINE_ENQUEUED, new InputLineEnqueuedEvent($value));
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        try {
            $commandLine = parent::dequeue();
        } catch (\RuntimeException $e) {
            $this->eventDispatcher->dispatch(EventsName::QUEUE_IS_EMPTY, new EmptiedQueueEvent());
            throw $e;
        }
        $this->eventDispatcher->dispatch(EventsName::INPUT_LINE_DEQUEUED, new InputLineDequeuedEvent($commandLine));

        return $commandLine;
    }

    /**
     * {@inheritdoc}
     */
    public function randomize()
    {
        $newQueue = parent::randomize();

        return new self($this->eventDispatcher, $newQueue);
    }

    /**
     * {@inheritdoc}
     */
    public function freeze()
    {
        if (parent::isFrozen()) {
            return;
        }

        parent::freeze();
        $this->eventDispatcher->dispatch(EventsName::QUEUE_IS_FROZEN, new FrozenQueueEvent());
    }
}
