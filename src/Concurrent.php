<?php

namespace Liuggio\Concurrent;

use Liuggio\Concurrent\Consumer\ConsumerListener;
use Liuggio\Concurrent\Event\EventsName;
use Liuggio\Concurrent\Process\ClosureProcess;
use Liuggio\Concurrent\Process\Channel\Channel;
use Liuggio\Concurrent\Process\ClosureProcessFactory;
use Liuggio\Concurrent\Process\Processes;
use Liuggio\Concurrent\Process\ProcessFactory;
use Liuggio\Concurrent\Queue\EventDispatcherQueue;
use Liuggio\Concurrent\Queue\QueueInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Concurrent
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * Concurrent constructor.
     *
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param null|string                   $autoloadFile
     */
    public function __construct(EventDispatcherInterface $eventDispatcher = null, $autoloadFile = null)
    {
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
        $this->processNeedToBeInitialized = true;
        $this->loopRunning = false;
        $this->autoloadFile = $autoloadFile ?: $this->findAutoloadFilename();
    }

    /**
     * Creates a concurrent loop for Callable processes.
     *
     * @param \Iterator|array|QueueInterface $data
     * @param \Closure                       $closure
     * @param int|float|null                 $pollingTime
     * @param int|null                       $forceToNChannels
     *
     * @return ConcurrentLoop
     *                        *
     *
     * @api
     */
    public function closures($data, \Closure $closure, $pollingTime = null, $forceToNChannels = null, $timeout = null)
    {
        $data = $this->createAQueueFromData($data);

        $factory = new ClosureProcessFactory($closure, $this->autoloadFile, $timeout);
        $consumer = new ConsumerListener($data, $this->eventDispatcher, $factory);

        $processes = $this->initProcesses($pollingTime, $forceToNChannels);
        $this->addConsumerToListener($consumer);
        $data->freeze();

        return new ConcurrentLoop($processes, $this->eventDispatcher);
    }

    /**
     * Creates the ConcurrentLoop for isolated Processes.
     *
     * @param \Iterator|array|QueueInterface $data
     * @param string                         $template
     * @param int|null                       $pollingTime
     * @param int|null                       $forceToNChannels
     * @param string|null                    $cwd
     *
     * @return ConcurrentLoop
     *
     * @api
     */
    public function processes($data, $template, $pollingTime = null, $forceToNChannels = null, $timeout = null, $cwd = null)
    {
        $data = $this->createAQueueFromData($data);
        $processFactory = new ProcessFactory(null, $timeout);
        $consumer = new ConsumerListener($data, $this->eventDispatcher, $processFactory, $template, $cwd);
        $processes = $this->initProcesses($pollingTime, $forceToNChannels);
        $this->addConsumerToListener($consumer);
        $data->freeze();

        return new ConcurrentLoop($processes, $this->eventDispatcher);
    }

    /**
     * Spawns a callable into an isolated processes.
     *
     * @param array|mixed    $args
     * @param \Closure       $closure
     * @param int|float|null $timeout
     * @param string|null    $cwd
     *
     * @return ClosureProcess
     *
     * @api
     */
    public function spawn($args, \Closure $closure, $timeout = null, $cwd = null)
    {
        $factory = new ClosureProcessFactory($closure, $this->autoloadFile, $timeout);

        $process = $factory->create(Channel::createAWaiting(0, 0), $args, 1, null, $cwd);
        $process->start();

        return $process;
    }

    private function initProcesses($pollingTime, $forceToNChannels)
    {
        $processes = new Processes($this->eventDispatcher, $pollingTime, $forceToNChannels);
        $this->eventDispatcher->addSubscriber($processes);

        return $processes;
    }

    private function addConsumerToListener($consumer)
    {
        $this->eventDispatcher->addListener(
            EventsName::CHANNEL_IS_WAITING,
            array($consumer, 'onChannelIsWaiting')
        );
    }

    private function findAutoloadFilename()
    {
        foreach (array(__DIR__.'/../../autoload.php', __DIR__.'/../vendor/autoload.php', __DIR__.'/vendor/autoload.php') as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }
    }

    private function createAQueueFromData($data)
    {
        if (!($data instanceof EventDispatcherQueue)) {
            $data = new EventDispatcherQueue($this->eventDispatcher, $data);

            return $data;
        }

        return $data;
    }
}
