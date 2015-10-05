<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\CommandLine;

interface ProcessInterface
{
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
