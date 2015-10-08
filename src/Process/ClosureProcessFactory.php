<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\CommandLine;
use Liuggio\Spawn\Process\Channel\Channel;
use SuperClosure\Serializer;

class ClosureProcessFactory implements ProcessFactoryInterface
{
    /**
     * @var string
     */
    private $autoload;

    /**
     * @var Serializer
     */
    private $fnSerializer;

    /**
     * @var callable
     */
    private $templateEngine;

    /**
     * @var int|float|null
     */
    private $timeout;

    /**
     * @param \Closure        $callable
     * @param string          $autoload
     * @param Serializer|null $fnSerializer
     * @param int|float|null  $timeout
     * @param callable|null   $templateEngine
     */
    public function __construct(
        \Closure $callable,
        $autoload,
        Serializer $fnSerializer = null,
        $timeout = null,
        callable $templateEngine = null
    ) {
        $this->autoload = $autoload;
        $this->fnSerializer = $fnSerializer ?: new Serializer();
        $this->templateEngine = $templateEngine ?: $this->createDefaultTemplateEngine($callable);
        $this->timeout = $timeout;
    }

    /**
     * @param Channel     $channel
     * @param mixed       $inputLine
     * @param int         $processCounter
     * @param string|null $template
     * @param string|null $cwd
     *
     * @return ClosureProcess
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
     * @param CommandLine        $commandLine
     * @param ProcessEnvironment $environment
     * @param string|null        $cwd
     * 
     * @return ClosureProcess
     */
    private function createProcess(CommandLine $commandLine, ProcessEnvironment $environment, $cwd = null)
    {
        return new ClosureProcess($commandLine, $environment, $this->timeout, $cwd);
    }

    /**
     * @param \Closure $closure
     * 
     * @return string
     */
    private function createDefaultTemplateEngine(\Closure $closure)
    {
        return $this->createDefaultFromSerializedClosure($this->fnSerializer->serialize($closure));
    }

    /**
     * @param mixed $serializedClosure
     * 
     * @return string
     */
    private function createDefaultFromSerializedClosure($serializedClosure)
    {
        $serializedClosure = base64_encode($serializedClosure);
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
$c = \Liuggio\Spawn\Process\ClosureReturnValue::start();
$s = new \SuperClosure\Serializer();
$fn = $s->unserialize(base64_decode("%s"));
$args = unserialize(base64_decode("%s"));
echo $c->stop(%s($fn, $args));
', $autoload, $serializedClosure, $input, $function);
        };
    }
}
