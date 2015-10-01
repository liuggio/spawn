<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\CommandLine;
use Liuggio\Spawn\Process\Channel\Channel;
use Symfony\Component\Process\PhpProcess;

class ClosureProcess extends PhpProcess
{
    /**
     * @var ProcessEnvironment
     */
    private $processEnvironment;

    /**
     * @param CommandLine        $script
     * @param ProcessEnvironment $processEnvironment
     * @param int|float|null     $timeout
     * @param string|null        $cwd
     */
    public function __construct(
        CommandLine $script,
        ProcessEnvironment $processEnvironment,
        $timeout = null,
        $cwd = null
    ) {
        $this->processEnvironment = $processEnvironment;
        parent::__construct((string) $script, null, $this->processEnvironment->exportToEnvsArray());
        if (!$timeout) {
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
        return $this->processEnvironment->getArguments();
    }

    /**
     * Waits for the process to terminate.
     *
     * The callback receives the type of output (out or err) and some bytes
     * from the output in real-time while writing the standard input to the process.
     * It allows to have feedback from the independent process during execution.
     *
     * @param callable|null $callback A valid PHP callback
     *
     * @throws RuntimeException When process timed out
     * @throws RuntimeException When process stopped after receiving signal
     * @throws LogicException   When process is not yet started
     *
     * @return int The returnValue of the Closure
     */
    public function wait($callback = null)
    {
        parent::wait($callback);

        return $this->getReturnValue();
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
     * The channel where the processes is executed.
     *
     * @return Channel
     */
    public function getChannel()
    {
        return $this->processEnvironment->getChannel();
    }

    /**
     * The output of the callable.
     *
     * @return string
     */
    public function getOutput()
    {
        $value = ClosureReturnValue::unserialize(parent::getOutput());

        return $value->getOutput();
    }

    /**
     * The return value of the callable.
     *
     * @return mixed
     */
    public function getReturnValue()
    {
        $value = ClosureReturnValue::unserialize(parent::getOutput());

        return $value->getReturnValue();
    }
}
