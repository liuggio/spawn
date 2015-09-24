<?php

namespace Liuggio\Fastest\Producer;


use Liuggio\Fastest\Queue\SplQueue;

class PhpUnitProducerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldRetrieveABunchOfInputLines()
    {
        $queue = new SplQueue();
        $producer = new PhpUnitProducer(__DIR__.'/../../phpunit.xml.dist');
        $producer->produce($queue);

        $this->assertGreaterThan(0, $queue->count());
    }
}
