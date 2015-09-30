<?php

include __DIR__.'/../vendor/autoload.php';

use Liuggio\Spawn\Spawn;

$testRunner = new Spawn();

$iterator = new \File_Iterator_Facade();
$files = $iterator->getFilesAsArray([__DIR__], ['Test.php']);

$exit = $testRunner
    ->processes($files, __DIR__.'/../bin/phpunit {}')
    ->onCompleted(function (\Liuggio\Spawn\Process\Process $process) {

        echo $process->getCommandLine();
        if ($process->getExitCode() == 0) {
            echo '  yes'.PHP_EOL;

            return;
        }
        echo '  ops'.PHP_EOL;
        echo $process->getErrorOutput().PHP_EOL;
        echo $process->getOutput().PHP_EOL;
        echo '====='.PHP_EOL;
    })
    ->onLoopCompleted(function ($exitCode, \Symfony\Component\Stopwatch\StopwatchEvent $event) {

        echo PHP_EOL.PHP_EOL.(($exitCode == 0) ? 'successful' : 'failed').PHP_EOL;
        echo 'memory used: '.$event->getMemory().PHP_EOL;
        echo 'Duration:   '.$event->getDuration().PHP_EOL;
    })
    ->start();

exit($exit);
// if you want more fun please have a look to the fastest project // https://github.com/liuggio/fastest

