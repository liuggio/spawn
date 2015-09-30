<?php

namespace Liuggio\Spawn;

use Liuggio\Spawn\Event\EmptiedQueueEvent;
use Liuggio\Spawn\Event\EventsName;
use Liuggio\Spawn\Event\LoopCompletedEvent;
use Liuggio\Spawn\Event\ProcessCompletedEvent;
use Liuggio\Spawn\Event\ProcessGeneratedBufferEvent;
use Liuggio\Spawn\Exception\LoopAlreadyStartedException;
use Liuggio\Spawn\Process\Processes;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SpawnLoop
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var Processes */
    private $processes;
    /** @var bool */
    private $loopRunning;

    public function __construct(Processes $processes, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->processes = $processes;
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
        $this->loopRunning = false;
    }

    /**
     * Start the Loop and wait.
     *
     * @param callable|null $callable
     *
     * @return $this
     *
     * @throws LoopAlreadyStartedException
     *
     * @api
     */
    public function start(callable $callable = null)
    {
        $this->assertLoopNotStarted();
        if (null != $callable) {
            $this->onCompleted($callable);
        }
        $this->loopRunning = true;
        $exitCode = $this->processes->loop();
        $this->loopRunning = false;

        return $exitCode;
    }

    /**
     * Add the callable as listener.
     *
     * @param callable $callable
     *
     * @return $this
     *
     * @api
     */
    public function onCompleted(callable $callable)
    {
        $this->addListener($callable, EventsName::PROCESS_COMPLETED);

        return $this;
    }

    /**
     * Add the callable as listener.
     *
     * @param callable $callable
     *
     * @return $this
     *
     * @api
     */
    public function onSuccessful(callable $callable)
    {
        $this->addListener($callable, EventsName::PROCESS_COMPLETED_SUCCESSFUL);

        return $this;
    }

    /**
     * Add the callable as listener.
     *
     * @param callable $callable
     *
     * @return $this
     *
     * @api
     */
    public function onStarted(callable $callable)
    {
        $this->addListener($callable, EventsName::PROCESS_STARTED);

        return $this;
    }

    /**
     * Add the callable as listener.
     *
     * @param callable $callable
     *
     * @return $this
     *
     * @api
     */
    public function onEmptyIterator(callable $callable)
    {
        $this->assertLoopNotStarted();

        $this->eventDispatcher->addListener(EventsName::QUEUE_IS_EMPTY,
            function (EmptiedQueueEvent $event) use ($callable) {
                $callable();
            }
        );

        return $this;
    }

    /**
     * Add the callable as listener.
     *
     * @param callable $callable
     *
     * @return $this
     *
     * @api
     */
    public function onLoopCompleted(callable $callable)
    {
        $this->assertLoopNotStarted();
        $this->eventDispatcher->addListener(EventsName::LOOP_COMPLETED,
            function (LoopCompletedEvent $event) use ($callable) {
                $callable($event->getExitCode(), $event->getStopwatchEvent());
            }
        );

        return $this;
    }

    /**
     * Add the callable as listener.
     *
     * @param callable $callable
     *
     * @return $this
     *
     * @api
     */
    public function onPartialOutput(callable $callable)
    {
        $this->assertLoopNotStarted();
        $this->eventDispatcher->addListener(EventsName::PROCESS_GENERATED_BUFFER,
            function (ProcessGeneratedBufferEvent $event) use ($callable) {
                $callable($event->getProcess());
            }
        );

        return $this;
    }

    private function assertLoopNotStarted()
    {
        if ($this->loopRunning) {
            throw new LoopAlreadyStartedException();
        }
    }

    private function addListener(callable $callable, $eventName)
    {
        $this->assertLoopNotStarted();
        $this->eventDispatcher->addListener($eventName,
            function (ProcessCompletedEvent $event) use ($callable) {
                    $callable($event->getProcess());
            }
        );
    }
}
