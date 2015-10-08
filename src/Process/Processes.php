<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\Event\ChannelIsWaitingEvent;
use Liuggio\Spawn\Event\EmptiedQueueEvent;
use Liuggio\Spawn\Event\EventsName;
use Liuggio\Spawn\Event\FrozenQueueEvent;
use Liuggio\Spawn\Event\LoopCompletedEvent;
use Liuggio\Spawn\Event\LoopStartedEvent;
use Liuggio\Spawn\Event\ProcessCompletedEvent;
use Liuggio\Spawn\Event\ProcessGeneratedBufferEvent;
use Liuggio\Spawn\Event\ProcessStartedEvent;
use Liuggio\Spawn\Process\Channel\Channels;
use Liuggio\Spawn\ProcessorCounter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class Processes implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var int|float
     */
    private $pollingTime;

    /**
     * @var int|float|null
     */
    private $parallelChannels;

    /**
     * @var Callable
     */
    private $exitCodeStrategy;

    /**
     * @var Channels
     */
    private $channels;

    /**
     * @var bool
     */
    private $queueIsEmpty = false;

    /**
     * @var bool
     */
    private $queueIsFrozen = false;

    /**
     * @var int
     */
    private $exitCode = 0;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param int                      $pollingTime
     * @param int                      $forceToUseNChannels
     * @param int|null                 $exitCodeStrategy
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        $pollingTime = 0,
        $forceToUseNChannels = 0,
        $exitCodeStrategy = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->pollingTime = $pollingTime ?: 200;
        $this->parallelChannels = $this->calculateChannels($forceToUseNChannels);
        $this->exitCodeStrategy = $this->createExitStrategyCallable($exitCodeStrategy);
        $this->channels = Channels::createWaiting($this->parallelChannels);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EventsName::QUEUE_IS_FROZEN => ['onFrozenQueue', 100],
            EventsName::QUEUE_IS_EMPTY => ['onQueueEmptied', 100],
            EventsName::PROCESS_STARTED => ['onProcessStarted', 100],
            EventsName::PROCESS_COMPLETED => ['onProcessCompleted', 100],
        ];
    }

    /**
     * @param FrozenQueueEvent $event
     */
    public function onFrozenQueue(FrozenQueueEvent $event)
    {
        $this->queueIsFrozen = true;
    }

    /**
     * @param EmptiedQueueEvent $event
     */
    public function onQueueEmptied(EmptiedQueueEvent $event)
    {
        $this->queueIsEmpty = true;
    }

    /**
     * @param ProcessStartedEvent $event
     */
    public function onProcessStarted(ProcessStartedEvent $event)
    {
        $channel = $event->getProcess()->getChannel();
        $this->channels->assignAProcess($channel, $event->getProcess());
    }

    /**
     * @param ProcessCompletedEvent $event
     */
    public function onProcessCompleted(ProcessCompletedEvent $event)
    {
        $channel = $event->getProcess()->getChannel();
        $exitCode = $event->getProcess()->getExitCode();
        $exitCodeStrategy = $this->exitCodeStrategy;
        $this->exitCode = $exitCodeStrategy($this->exitCode, $exitCode);

        $this->channels->setEmpty($channel);
        $this->eventDispatcher->dispatch(EventsName::CHANNEL_IS_WAITING, new ChannelIsWaitingEvent($channel));
    }

    /**
     * @return int
     */
    public function loop()
    {
        $stopWatch = new Stopwatch();
        $stopWatch->start('loop');
        $this->eventDispatcher->dispatch(EventsName::LOOP_STARTED, new LoopStartedEvent($this->parallelChannels));
        $this->notifyWaitingChannel($this->channels->getWaitingChannels());
        while (!($this->queueIsFrozen && $this->queueIsEmpty && count($this->channels->getAssignedChannels()) < 1)) {
            $this->checkTerminatedProcessOnChannels($this->channels->getAssignedChannels());
            usleep($this->pollingTime);
        }
        $stopWatchEvent = $stopWatch->stop('loop');
        $this->eventDispatcher->dispatch(EventsName::LOOP_COMPLETED, new LoopCompletedEvent($stopWatchEvent, $this->exitCode));

        return $this->exitCode;
    }

    /**
     * @param Channel[] $waitingChannels
     */
    private function notifyWaitingChannel($waitingChannels)
    {
        foreach ($waitingChannels as $channel) {
            $this->eventDispatcher->dispatch(
                EventsName::CHANNEL_IS_WAITING,
                new ChannelIsWaitingEvent($channel)
            );
        }
    }

    /**
     * @param Channel[] $assignedChannels
     */
    private function checkTerminatedProcessOnChannels($assignedChannels)
    {
        foreach ($assignedChannels as $channel) {
            /** @var Process|ClosureProcess $process */
            $process = $channel->getProcess();

            $this->eventDispatcher->dispatch(
                EventsName::PROCESS_GENERATED_BUFFER,
                new ProcessGeneratedBufferEvent($process)
            );
            if (!$process->isTerminated()) {
                continue;
            }

            $this->eventDispatcher->dispatch(EventsName::PROCESS_COMPLETED, new ProcessCompletedEvent($process));
            if ($process->isSuccessful()) {
                $this->eventDispatcher->dispatch(EventsName::PROCESS_COMPLETED_SUCCESSFUL, new ProcessCompletedEvent($process));
            }
        }
    }

    /**
     * @param int $forceToUseNChannels
     * 
     * @return int
     */
    private function calculateChannels($forceToUseNChannels = 0)
    {
        if ((int) $forceToUseNChannels > 0) {
            return $forceToUseNChannels;
        }
        $processorCounter = new ProcessorCounter();

        return $processorCounter->execute();
    }

    /**
     * @param callable|null $exitStrategyCallable
     * 
     * @return callable|int
     */
    private function createExitStrategyCallable(callable $exitStrategyCallable = null)
    {
        if (null != $exitStrategyCallable) {
            return $exitStrategyCallable;
        }

        return function ($current, $exitCode) {

            return ($current == 0 && $exitCode == 0) ? 0 : $exitCode;
        };
    }
}
