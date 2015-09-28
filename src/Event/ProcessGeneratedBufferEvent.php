<?php

namespace Liuggio\Concurrent\Event;

use Liuggio\Concurrent\Process\ClosureProcess;
use Liuggio\Concurrent\Process\Process;
use Symfony\Component\EventDispatcher\Event;

final class ProcessGeneratedBufferEvent extends Event
{
    /** @var Process|ClosureProcess */
    private $process;

    public function __construct($process)
    {
        $this->process = $process;
    }

    public function getProcess()
    {
        return $this->process;
    }

    public function getIncrementalOutput()
    {
        return $this->process->getIncrementalOutput();
    }

    public function getIncrementalErrorOutput()
    {
        return $this->process->getIncrementalErrorOutput();
    }
}
