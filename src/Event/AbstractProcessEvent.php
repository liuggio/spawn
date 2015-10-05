<?php

namespace Liuggio\Spawn\Event;

use Liuggio\Spawn\Process\ProcessInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractProcessEvent extends Event
{
    /**
     * @var ProcessInterface
     */
    protected $process;

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
}
