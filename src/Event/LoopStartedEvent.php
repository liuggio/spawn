<?php

namespace Liuggio\Spawn\Event;

use Symfony\Component\EventDispatcher\Event;

class LoopStartedEvent extends Event
{
    /** @var int */
    private $channelsNumber;

    /**
     * LoopStartedEvent constructor.
     *
     * @param int $channelsNumber
     */
    public function __construct($channelsNumber)
    {
        $this->channelsNumber = $channelsNumber;
    }

    /**
     * @return int
     */
    public function getChannelsNumber()
    {
        return $this->channelsNumber;
    }
}
