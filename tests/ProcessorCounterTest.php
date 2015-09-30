<?php

namespace Liuggio\Spawn;

class ProcessorCounterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCountTheNumberOfProcessorInLinux()
    {
        $path = __DIR__.'/Fixture/proc_cpuinfo';
        ProcessorCounter::$count = null;
        $processorCount = new ProcessorCounter($path, 'Linux');
        $this->assertEquals(4, $processorCount->execute());
    }
}
