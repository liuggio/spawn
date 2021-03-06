<?php

namespace Liuggio\Spawn\Process\Channel;

use Liuggio\Spawn\Process\ProcessInterface;

class Channels
{
    /**
     * @var array
     */
    private $channels = [];

    /**
     * @param int $channelsNumber
     */
    private function __construct($channelsNumber)
    {
        $channelsNumber = (int) $channelsNumber;
        for ($i = 0; $i < $channelsNumber; ++$i) {
            $channel = Channel::createAWaiting($i, $channelsNumber);
            $this->channels[$channel->getId()] = $channel;
        }
    }

    /**
     * Creates a bunch of waiting channels.
     *
     * @param int $channelsNumber
     *
     * @return Channels
     */
    public static function createWaiting($channelsNumber)
    {
        return new self($channelsNumber);
    }

    /**
     * Assign a channel to a processes.
     *
     * @param Channel          $channel
     * @param ProcessInterface $process
     */
    public function assignAProcess(Channel $channel, ProcessInterface $process)
    {
        $this->channels[$channel->getId()] = $channel->assignToAProcess($process);
    }

    /**
     * Free a channel.
     *
     * @param Channel $channel
     */
    public function setEmpty(Channel $channel)
    {
        $this->channels[$channel->getId()] = $channel->setIsWaiting();
    }

    /**
     * Array of all the waiting channels.
     *
     * @return Channel[]
     */
    public function getWaitingChannels()
    {
        return array_values(array_filter($this->channels, function (Channel $channel) {
            return $channel->isWaiting();
        }));
    }

    /**
     * Array of all the assigned channels.
     *
     * @return Channel[]
     */
    public function getAssignedChannels()
    {
        return array_values(array_filter($this->channels, function (Channel $channel) {
            return !$channel->isWaiting();
        }));
    }
}
