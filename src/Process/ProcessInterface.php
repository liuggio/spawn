<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\CommandLine;

interface ProcessInterface
{
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
    );

    /**
     * @return mixed
     */
    public function getInputLine();

    /**
     * @return CommandLine
     */
    public function getCommandLine();

    /**
     * The current Id of the processes.
     *
     * @return int
     */
    public function getIncrementalNumber();

    /**
     * The channel where the process is executed.
     *
     * @return Channel
     */
    public function getChannel();
}
