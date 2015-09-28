<?php

namespace Liuggio\Concurrent\Event;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractInputLineEvent extends Event
{
    /** @var mixed */
    private $inputLine;

    /**
     * NewCommandLoadedEvent constructor.
     *
     * @param $command
     */
    public function __construct($command)
    {
        $this->inputLine = $command;
    }

    /**
     * @return mixed
     */
    public function getInputLine()
    {
        return $this->inputLine;
    }
}
