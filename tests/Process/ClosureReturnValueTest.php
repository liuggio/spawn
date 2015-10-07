<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\Process\ClosureReturnValue;

class ClosureReturnValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function mustUnserializeCorrectly()
    {
        ClosureReturnValue::unserialize(base64_encode(serialize(array_fill(0, 5, null))));
    }

    /**
     * @covers            \Liuggio\Spawn\Process\ClosureReturnValue::unserialize
     * @dataProvider      mustUnserializeUncorrectlyProvider
     * @expectedException \Liuggio\Spawn\Exception\InvalidArgumentException
     * @test
     */
    public function mustUnserializeUncorrectly($toSerialize)
    {
        ClosureReturnValue::unserialize(base64_encode(serialize($toSerialize)));
    }

    /**
     * @return array
     */
    public function mustUnserializeUncorrectlyProvider()
    {
        return array(
            array('foo'),
            array(5),
            array(array()),
            array(array_fill(0, 4, null)),
            array(null),
        );
    }
}
