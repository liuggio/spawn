<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\CommandLine;
use Symfony\Component\Process\Process as BaseProcess;

class Process extends BaseProcess implements ProcessInterface
{
    /**
     * @var ProcessEnvironment
     */
    private $processEnvironment;

    /**
     * @param CommandLine        $commandLine
     * @param ProcessEnvironment $processEnvironment
     * @param int|float|null     $timeout
     * @param string|null        $cwd
     */
    public function __construct(
        CommandLine $commandLine,
        ProcessEnvironment $processEnvironment,
        $timeout = null,
        $cwd = null
    ) {
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

    /**
     * The current Id of the processes.
     *
     * @return int
     */
    public function getIncrementalNumber()
    {
        return $this->processEnvironment->getIncrementalNumber();
    }

    /**
     * The channel where the process is executed.
     *
     * @return Channel
     */
    public function getChannel()
    {
        return $this->processEnvironment->getChannel();
    }
}
