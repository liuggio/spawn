<?php

namespace Liuggio\Concurrent\Process;

use Liuggio\Concurrent\CommandLine;
use Liuggio\Concurrent\Process\Channel\Channel;
use SuperClosure\Serializer;

class ClosureProcessFactory implements ProcessFactoryInterface
{
    /** @var string */
    private $autoload;
    /** @var Serializer */
    private $fnSerializer;
    /** @var callable */
    private $templateEngine;
    /** @var int */
    private $processCounter;
    /** @var int|float|null */
    private $timeout;

    public function __construct(\Closure $callable, $autoload, Serializer $fnSerializer = null, $timeout = null, callable $templateEngine = null)
    {
        $this->autoload = $autoload;
        $this->fnSerializer = $fnSerializer ?: new Serializer();
        $this->processCounter = 0;
        $this->templateEngine = $templateEngine ?: $this->createDefaultTemplateEngine($callable);
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

    private function createProcess(CommandLine $commandLine, ProcessEnvironment $environment, $cwd = null)
    {
        return new ClosureProcess($commandLine, $environment, $this->timeout, $cwd);
    }

    private function createDefaultTemplateEngine(\Closure $closure)
    {
        return $this->createDefaultFromSerializedClosure($this->fnSerializer->serialize($closure));
    }

    private function createDefaultFromSerializedClosure($serializedClosure)
    {
        $serializedClosure = base64_encode(serialize($serializedClosure));
        $autoload = $this->autoload;

        return function (ProcessEnvironment $envs, $template) use ($serializedClosure, $autoload) {

            $arguments = $envs->getArguments();
            $function = 'call_user_func';
            if (is_array($arguments)) {
                $function = 'call_user_func_array';
            }

            $input = base64_encode(serialize($envs->getArguments()));

            return sprintf('<?php
require_once \'%s\';
$c = \Liuggio\Concurrent\Process\ClosureReturnValue::start();
$s = new \SuperClosure\Serializer();
$fn = $s->unserialize(unserialize(base64_decode("%s")));
$args = unserialize(base64_decode("%s"));
echo $c->stop(%s($fn, $args));
', $autoload, $serializedClosure, $input, $function);
        };
    }
}
