<?php

namespace Liuggio\Concurrent\Event;

use Liuggio\Concurrent\Process\Channel\Channel;
use Symfony\Component\EventDispatcher\Event;

class ChannelIsWaitingEvent extends Event
{
    /** @var  Channel */
    private $channel;

    /**
     * CommandLineAssignedToAChannel constructor.
     *
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
