<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\CommandLine;
use Symfony\Component\Process\Process as BaseProcess;

class Process extends BaseProcess
{
    /** @var ProcessEnvironment */
    private $processEnvironment;

    public function __construct(
        CommandLine $commandLine,
        ProcessEnvironment $processEnvironment,
        $timeout = null,
        $cwd = null)
    {
        $this->processEnvironment = $processEnvironment;

        parent::__construct((string) $commandLine, $cwd, $this->processEnvironment->exportToEnvsArray());
        if ($timeout) {
            $this->setTimeout($timeout);
           // compatibility to SF 2.2
            if (method_exists($this, 'setIdleTimeout')) {
                $this->setIdleTimeout($timeout);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getInputLine()
    {
        return new $this->processEnvironment->getInputLine();
    }

    /**
     * @return CommandLine
     */
    public function getCommandLine()
    {
        return new CommandLine(parent::getCommandLine());
    }

    public function getIncrementalNumber()
    {
        return $this->processEnvironment->getIncrementalNumber();
    }

    public function getChannel()
    {
        return $this->processEnvironment->getChannel();
    }
}
