Concurrent handling of PHP processes (and also closures)
========================================================

[![Build Status](https://travis-ci.org/liuggio/spawn.svg)](https://travis-ci.org/liuggio/spawn)

The main job of Spawn is to improve the performance handling concurrent processes.

This library tries to do its best to speedup things using all the cores of the machine
useful when you need to run bunch of commands
eg:  unit-tests/functional-tests/CS fixes/files handling

``` php
$spawn = new Spawn();
$spawn
    ->processes(range(1,10), "printenv > '/tmp/envs_{}{p}.log';")
    ->onCompleted(function(Process $singleProcess){ /* print stats */});
    ->start();
```

## Concurrent \Closure? Really?

Yes! With this library you can use concurrent closures,
**BUT** PHP is not `Go-lang` neither `Erlang` or any other famous language for concurrency,
and in order to simulate a isolated routine the closure is serialized and executed in a new PhpProcess,
be aware this is a workaround in order to speed up your concurrent Closures.

With this library you can also do:

1. executes and handles **concurrent PHP closures**.
2. **spawns** a single closure as an independent process.

### Concurrent closures: Upload images to your CDN

Feed an iterator and it will break the job into multiple php scripts and spread them across many processes.
In order to improve performances, the number of processes is equal to the number of computer's cores.

``` php
$spawn = new Spawn();

$files = new RecursiveDirectoryIterator('/path/to/images');
$files = new RecursiveIteratorIterator($files);

$spawn->closures($files, function(SplFileInfo $file) {
    // upload this file
})
->start();
```

Each closure is executed in isolation using the [PhpProcess](http://symfony.com/doc/current/components/process.html#executing-php-code-in-isolation) component.

### Spawn a single isolated closure

``` php
$spawn = new Spawn();
$sum = 3;

$processes = $spawn
    ->spawn(["super", 120], function($prefix, $number) use ($sum) {
        echo $prefix." heavy routine";
        return $number+$sum;
    });
    
// do something else here
echo $processes->wait();      // 123
echo $processes->getOutput(); // "super heavy routine"
```

### Advanced

1. The callable is executed in a new isolated processes also with its "use" references.
2. It's possible to add a listener for event handling.
3. It's possible to get the return value of each callable, the ErrorOutput, the Output and other information.

``` php
$collaborator = new YourCollaborator(1,2,3,4);

$spawn
    ->closures(range(1, 7), function($input) use ($collaborator) {
        echo "this is the echo";
        $collaborator->doSomething();
        $return = new \stdClass();
        $return->name = "name";

        return $return;
    })
    ->onCompleted(function(ClosureProcess $process){
        // do something with
        $returnValue = $processes->getReturnValue();
        $output      = $processes->getOutput();
        $errorOutput = $processes->getErrorOutput();
        $time        = $processes->startAt();
        $memory      = $processes->getMemory();
        $duration    = $processes->getDuration();
    })
    ->start();
```

### Events:

Listeners can be attached to `closures` and `processes`.

``` php
    ->onStarted(function(ClosureProcess|Process $process){});
    ->onCompleted(function(ClosureProcess|Process $process){});
    ->onSuccessful(function(ClosureProcess|Process $process){});
    ->onEmptyIterator(function (){});
    ->onPartialOutput(function(ClosureProcess|Process $process){})
    ->onLoopCompleted(function ($exitCode, StopwatchEvent $event)
```

### Other libs:

There are not so many libraries that handle concurrent processes.
The best I found is about forking processes [spork](https://github.com/kriswallsmith/spork)
it features a great API and with no work-around but it needs several PHP extensions.

### License:

MIT License see the [License](./LICENSE).

### More fun?

- see how the [travis.yml](./.travis.yml#16) run test suite with [spawn_tests](./tests/spawn_tests.php).
- have fun with [fastest](https://github.com/liuggio/fastest)
