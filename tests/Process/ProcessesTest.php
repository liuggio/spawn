<?php

namespace Liuggio\Concurrent\Process;

use Liuggio\Concurrent\Event\EventsName;

class ProcessesTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldLoopUntilQueueisEmptiedAndFrozen()
    {
        $ed = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $ed->expects($this->at(0))
            ->method('dispatch')
            ->with($this->equalTo(EventsName::LOOP_STARTED));

        $ed->expects($this->at(1))
            ->method('dispatch')
            ->with($this->equalTo(EventsName::CHANNEL_IS_WAITING));

        $ed->expects($this->at(2))
            ->method('dispatch')
            ->with($this->equalTo(EventsName::CHANNEL_IS_WAITING));

        $ed->expects($this->at(3))
            ->method('dispatch')
            ->with($this->equalTo(EventsName::LOOP_COMPLETED));

        $processes = new Processes($ed, null, 2);

        $ev = $this->getMock('\Liuggio\Concurrent\Event\FrozenQueueEvent');
        $processes->onFrozenQueue($ev);
        $ev2 = $this->getMock('\Liuggio\Concurrent\Event\EmptiedQueueEvent');
        $processes->onQueueEmptied($ev2);

        $processes->loop();
    }
}
