<?php

namespace Liuggio\Spawn\Consumer;

use Liuggio\Spawn\CommandLine;
use Liuggio\Spawn\Event\ChannelIsWaitingEvent;
use Liuggio\Spawn\Process\Channel\Channel;

class ConsumerListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function onChannelWaitABeforeCommandLineShouldExecuteANewProcess()
    {
        $process = $this->getMockBuilder('\Liuggio\Spawn\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $channel = Channel::createAWaiting(3, 5);

        $queue = $this->getMock('\Liuggio\Spawn\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('dequeue')
            ->willReturn(10);

        $processFactory = $this->getMock('\Liuggio\Spawn\Process\ProcessFactory');
        $processFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo($channel))
            ->willReturn($process);

        $ed = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $consumer = new ConsumerListener($queue, $ed, $processFactory, CommandLine::fromString("echo 'a'"));
        $consumer->onChannelIsWaiting(new ChannelIsWaitingEvent($channel));
    }
}
