<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\CommandLine;
use Liuggio\Spawn\Process\Channel\Channel;

class ProcessFactory implements ProcessFactoryInterface
{
    /**
     * @var callable
     */
    private $templateEngine;

    /**
     * @var int|float|null
     */
    private $timeout;

    /**
     * @param callable|null  $templateEngine
     * @param int|float|null $timeout
     */
    public function __construct(callable $templateEngine = null, $timeout = null)
    {
        $this->templateEngine = $templateEngine ?: $this->createDefaultTemplateEngine();
        $this->timeout = $timeout;
    }

    /**
     * @param Channel     $channel
     * @param mixed       $inputLine
     * @param int|null    $processCounter
     * @param string|null $template
     * @param string|null $cwd
     *
     * @return Process
     */
    public function create(Channel $channel, $inputLine, $processCounter, $template = null, $cwd = null)
    {
        $environment = new ProcessEnvironment($channel, $inputLine, $processCounter);
        $engine = $this->templateEngine;
        $commandLine = $engine($environment, (string) $template);
        if (is_string($commandLine)) {
            $commandLine = CommandLine::fromString($commandLine);
        }

        return $this->createProcess($commandLine, $environment, $cwd);
    }

    /**
     * @return callable
     */
    protected function createDefaultTemplateEngine()
    {
        return function (ProcessEnvironment $processEnvironment, $template) {
            $commandToExecute = str_replace('{}', (string) $processEnvironment->getArguments(), (string) $template);
            $commandToExecute = str_replace('{p}', $processEnvironment->getChannelId(), $commandToExecute);
            $commandToExecute = str_replace('{inc}', $processEnvironment->getIncrementalNumber(), $commandToExecute);

            return $commandToExecute;
        };
    }

    /**
     * @param CommandLine        $commandLine
     * @param ProcessEnvironment $environment
     * @param string             $cwd
     *
     * @return Process
     */
    protected function createProcess(CommandLine $commandLine, ProcessEnvironment $environment, $cwd = null)
    {
        return new Process($commandLine, $environment, $this->timeout, $cwd);
    }
}
