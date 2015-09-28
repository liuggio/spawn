<?php

namespace Liuggio\Concurrent\Process;

use Liuggio\Concurrent\Process\Channel\Channel;
use Liuggio\Concurrent\CommandLine;

class ProcessFactory implements ProcessFactoryInterface
{
    /** @var Callable */
    private $templateEngine;
    /** @var int */
    private $processCounter;
    /** @var int|float|null */
    private $timeout;

    public function __construct(callable $templateEngine = null, $timeout = null)
    {
        $this->processCounter = 0;
        $this->templateEngine = $templateEngine ?: $this->createDefaultTemplateEngine();
        $this->timeout = $timeout;
    }

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

    protected function createDefaultTemplateEngine()
    {
        return function (ProcessEnvironment $processEnvironment, $template) {
            $commandToExecute = str_replace('{}', (string) $processEnvironment->getArguments(), (string) $template);
            $commandToExecute = str_replace('{p}', $processEnvironment->getChannelId(), $commandToExecute);
            $commandToExecute = str_replace('{inc}', $processEnvironment->getIncrementalNumber(), $commandToExecute);

            return $commandToExecute;
        };
    }

    protected function createProcess(CommandLine $commandLine, ProcessEnvironment $environment, $cwd = null)
    {
        return new Process($commandLine, $environment, $this->timeout, $cwd);
    }
}
