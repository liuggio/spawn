<?php

namespace Liuggio\Concurrent\Process;

use Liuggio\Concurrent\Process\Channel\Channels;
use Liuggio\Concurrent\Event\ChannelIsWaitingEvent;
use Liuggio\Concurrent\Event\EmptiedQueueEvent;
use Liuggio\Concurrent\Event\EventsName;
use Liuggio\Concurrent\Event\FrozenQueueEvent;
use Liuggio\Concurrent\Event\LoopCompletedEvent;
use Liuggio\Concurrent\Event\LoopStartedEvent;
use Liuggio\Concurrent\Event\ProcessCompletedEvent;
use Liuggio\Concurrent\Event\ProcessGeneratedBufferEvent;
use Liuggio\Concurrent\Event\ProcessStartedEvent;
use Liuggio\Concurrent\ProcessorCounter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

class Processes implements EventSubscriberInterface
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var int|float */
    private $pollingTime;
    /** @var int|float|null */
    private $parallelChannels;
    /** @var Callable */
    private $exitCodeStrategy;
    /** @var Channels */
    private $channels;
    /** @var bool */
    private $queueIsEmpty;
    /** @var bool */
    private $queueIsFrozen;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        $pollingTime = null,
        $forceToUseNChannels = null,
        $exitCodeStrategy = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->pollingTime = $pollingTime ?: 200;
        $this->parallelChannels = $this->calculateChannels($forceToUseNChannels);
        $this->exitCodeStrategy = $this->createExitStrategyCallable($exitCodeStrategy);
        $this->exitCode = 0;
        $this->queueIsEmpty = false;
        $this->queueIsFrozen = false;
        $this->channels = Channels::createWaiting($this->parallelChannels);
    }

    public static function getSubscribedEvents()
    {
        return array(
            EventsName::QUEUE_IS_FROZEN => array('onFrozenQueue', 100),
            EventsName::QUEUE_IS_EMPTY => array('onQueueEmptied', 100),
            EventsName::PROCESS_STARTED => array('onProcessStarted', 100),
            EventsName::PROCESS_COMPLETED => array('onProcessCompleted', 100),
        );
    }

    public function onFrozenQueue(FrozenQueueEvent $event)
    {
        $this->queueIsFrozen = true;
    }

    public function onQueueEmptied(EmptiedQueueEvent $event)
    {
        $this->queueIsEmpty = true;
    }

    public function onProcessStarted(ProcessStartedEvent $event)
    {
        $channel = $event->getProcess()->getChannel();
        $this->channels->assignAProcess($channel, $event->getProcess());
    }

    public function onProcessCompleted(ProcessCompletedEvent $event)
    {
        $channel = $event->getProcess()->getChannel();
        $exitCode = $event->getProcess()->getExitCode();
        $exitCodeStrategy = $this->exitCodeStrategy;
        $this->exitCode = $exitCodeStrategy($this->exitCode, $exitCode);

        $this->channels->setEmpty($channel);
        $this->eventDispatcher->dispatch(EventsName::CHANNEL_IS_WAITING, new ChannelIsWaitingEvent($channel));
    }

    public function loop()
    {
        $stopWatch = new Stopwatch();
        $stopWatch->start('loop');
        $this->eventDispatcher->dispatch(EventsName::LOOP_STARTED, new LoopStartedEvent($this->parallelChannels));
        $this->notifyWaitingChannel($this->channels->getWaitingChannels());
        while (!($this->queueIsFrozen && $this->queueIsEmpty && count($assignedChannels = $this->channels->getAssignedChannels()) < 1)) {
            $this->checkTerminatedProcessOnChannels($this->channels->getAssignedChannels());
            usleep($this->pollingTime);
        }
        $stopWatchEvent = $stopWatch->stop('loop');
        $this->eventDispatcher->dispatch(EventsName::LOOP_COMPLETED, new LoopCompletedEvent($stopWatchEvent, $this->exitCode));

        return $this->exitCode;
    }

    private function notifyWaitingChannel($waitingChannels)
    {
        foreach ($waitingChannels as $channel) {
            $this->eventDispatcher->dispatch(
                EventsName::CHANNEL_IS_WAITING,
                new ChannelIsWaitingEvent($channel)
            );
        }
    }

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

    private function calculateChannels($forceToUseNChannels = 0)
    {
        if ((int) $forceToUseNChannels > 0) {
            return $forceToUseNChannels;
        }
        $processorCounter = new ProcessorCounter();

        return $processorCounter->execute();
    }

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
