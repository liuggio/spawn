<?php

namespace Liuggio\Spawn\Event;

use Liuggio\Spawn\Process\ClosureProcess;
use Liuggio\Spawn\Process\Process;
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
