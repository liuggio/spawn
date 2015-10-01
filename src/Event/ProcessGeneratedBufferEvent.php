<?php

namespace Liuggio\Spawn\Event;

use Liuggio\Spawn\Process\ClosureProcess;
use Liuggio\Spawn\Process\Process;
use Symfony\Component\EventDispatcher\Event;

final class ProcessGeneratedBufferEvent extends Event
{
    /**
     * @var Process|ClosureProcess
     */
    private $process;

    /**
     * @param Process|ClosureProcess $process
     */
    public function __construct($process)
    {
        $this->process = $process;
    }

    /**
     * @return Process|ClosureProcess
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return string
     */
    public function getIncrementalOutput()
    {
        return $this->process->getIncrementalOutput();
    }

    /**
     * @return string
     */
    public function getIncrementalErrorOutput()
    {
        return $this->process->getIncrementalErrorOutput();
    }
}
