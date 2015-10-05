<?php

namespace Liuggio\Spawn\Event;

use Liuggio\Spawn\Process\ProcessInterface;
use Symfony\Component\EventDispatcher\Event;

final class ProcessGeneratedBufferEvent extends Event
{
    /**
     * @var ProcessInterface
     */
    private $process;

    /**
     * @param ProcessInterface $process
     */
    public function __construct(ProcessInterface $process)
    {
        $this->process = $process;
    }

    /**
     * @return ProcessInterface
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
