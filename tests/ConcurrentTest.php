<?php

namespace Liuggio\Concurrent;

use Liuggio\Concurrent\Process\ClosureProcess;
use Liuggio\Concurrent\Process\Process;

class ConcurrentTest extends \PHPUnit_Framework_TestCase
{
    /**
     *  @test
     */
    public function shouldExecuteASimpleCallableAndGetTheOutputAndReturnValueCorrectly()
    {
        $concurrent = new Concurrent();
        $concurrent
            ->closures(range(1, 7), function ($input) {
                echo 'this is the echo';
                $return = new \stdClass();
                $return->name = 'name';

                return $return;
            })
            ->onCompleted(function (ClosureProcess $process) {
                $this->assertEquals('', $process->getErrorOutput());
                $this->assertEquals('name', $process->getReturnValue()->name);
                $this->assertEquals('this is the echo', $process->getOutput());
            })
            ->start();
    }

    /**
     *  @test
     */
    public function shouldExecuteAConcurrentProcesses()
    {
        $concurrent = new Concurrent();
        $concurrent
            ->processes([1], $template = "echo -n '{}';")
            ->onCompleted(function (Process $process) {
                $this->assertEquals(null, $process->getErrorOutput());
                $this->assertEquals('1', $process->getOutput());
            })
            ->start();
    }

    /**
     *  @test
     */
    public function shouldExecuteAndGetPartialBuffer()
    {
        $concurrent = new Concurrent();
        $concurrent
            ->processes([1], $template = "echo -n '{}';sleep 2;echo -n done")
            ->onPartialOutput(function (Process $process) {
                $this->assertEquals(null, $process->getErrorOutput());
                $output = $process->getIncrementalOutput();
                if (!empty($output)) {
                    $this->assertTrue((strpos('1done', $output) !== false), $output);
                }

            })
            ->onCompleted(function (Process $process) {
                $this->assertEquals(null, $process->getErrorOutput());
                $output = $process->getOutput();
                $this->assertTrue((strpos('1done', $output) !== false), $output);
            })
            ->start();
    }

    /**
     * @test
     */
    public function shouldSpawnACallable()
    {
        $sumAndPrint = function ($sum) {
            foreach (range(1, $sum) as $i) {
                echo "$i";
                $sum += $i;
            }

            return $sum;
        };

        $concurrent = new Concurrent();
        $process = $concurrent->spawn(10, $sumAndPrint);
        $process->wait();
        $this->assertEquals(null, $process->getErrorOutput());
        $this->assertEquals(65, $process->getReturnValue());
        $this->assertEquals('12345678910', $process->getOutput());
    }
}
