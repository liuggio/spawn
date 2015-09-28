<?php

namespace Liuggio\Concurrent\Process;

use Liuggio\Concurrent\Process\Channel\Channel;

class ProcessEnvironment
{
    const ENV_TEST_CHANNEL = 'ENV_TEST_CHANNEL';
    const ENV_TEST_CHANNEL_READABLE = 'ENV_TEST_CHANNEL_READABLE';
    const ENV_TEST_CHANNELS_NUMBER = 'ENV_TEST_CHANNELS_NUMBER';
    const ENV_TEST_ARGUMENT = 'ENV_TEST_ARGUMENT';
    const ENV_TEST_INCREMENTAL_NUMBER = 'ENV_TEST_INC_NUMBER';
    const ENV_TEST_IS_FIRST_ON_CHANNEL = 'ENV_TEST_IS_FIRST_ON_CHANNEL';

    /** @var Channel */
    private $channel;
    /** @var mixed */
    private $arguments;
    /** @var int */
    private $incrementNumber;

    /**
     * @param Channel $channel
     * @param mixed   $arguments
     * @param int     $incrementNumber
     */
    public function __construct(Channel $channel, $arguments, $incrementNumber)
    {
        $this->channel = $channel;
        $this->arguments = $arguments;
        $this->incrementNumber = $incrementNumber;
    }

    public function exportToEnvsArray()
    {
        return array(
            self::ENV_TEST_CHANNEL.'='.$this->channel->getId(),
            self::ENV_TEST_CHANNEL_READABLE.'='.$this->getReadableChannel(),
            self::ENV_TEST_CHANNELS_NUMBER.'='.$this->getChannelsNumber(),
            self::ENV_TEST_ARGUMENT.'='.$this->getArguments(),
            self::ENV_TEST_INCREMENTAL_NUMBER.'='.$this->getIncrementalNumber(),
            self::ENV_TEST_IS_FIRST_ON_CHANNEL.'='.(int) $this->isTheFirstCommandOnChannel(),
        );
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return Channel
     */
    public function getChannelId()
    {
        return $this->channel->getId();
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getReadableChannel()
    {
        return 'test_'.(int) $this->channel->getId();
    }

    /**
     * @return int
     */
    public function getChannelsNumber()
    {
        return $this->channel->getChannelsNumber();
    }

    /**
     * @return int
     */
    public function getIncrementalNumber()
    {
        return $this->incrementNumber;
    }

    /**
     * @return bool
     */
    public function isTheFirstCommandOnChannel()
    {
        return ($this->channel->getAssignedProcessesCounter() == 1);
    }
}
