<?php

namespace Liuggio\Concurrent\Consumer;

use Liuggio\Concurrent\CommandLine;
use Liuggio\Concurrent\Event\ChannelIsWaitingEvent;
use Liuggio\Concurrent\Process\Channel\Channel;

class ConsumerListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function onChannelWaitABeforeCommandLineShouldExecuteANewProcess()
    {
        $process = $this->getMockBuilder('\Liuggio\Concurrent\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $channel = Channel::createAWaiting(3, 5);

        $queue = $this->getMock('\Liuggio\Concurrent\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('dequeue')
            ->willReturn(10);

        $processFactory = $this->getMock('\Liuggio\Concurrent\Process\ProcessFactory');
        $processFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo($channel))
            ->willReturn($process);

        $ed = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $consumer = new ConsumerListener($queue, $ed, $processFactory, CommandLine::fromString("echo 'a'"));
        $consumer->onChannelIsWaiting(new ChannelIsWaitingEvent($channel));
    }
}
