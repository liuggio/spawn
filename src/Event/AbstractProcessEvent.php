<?php

namespace Liuggio\Concurrent\Event;

use Liuggio\Concurrent\Process\ClosureProcess;
use Liuggio\Concurrent\Process\Process;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractProcessEvent extends Event
{
    protected $process;

    public function __construct($process)
    {
        $this->process = $process;
    }

    /**
     * @return ClosureProcess|Process
     */
    public function getProcess()
    {
        return $this->process;
    }
}
