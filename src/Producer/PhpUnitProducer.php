<?php

namespace Liuggio\Fastest\Producer;


use Liuggio\Fastest\InputLine;
use Liuggio\Fastest\Queue\QueueInterface;

class PhpUnitProducer implements ProducerInterface
{
    /** @var QueueInterface */
    private $configurationFilename;

    /**
     * PhpUnitProducer constructor.
     */
    public function __construct($configurationFilename)
    {
        $this->configurationFilename = $configurationFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function produce(QueueInterface $queue)
    {
        try {
            $configuration = \PHPUnit_Util_Configuration::getInstance($this->configurationFilename);
        } catch (\Throwable $e) {
            print $e->getMessage() . "\n";
            exit(\PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
        } catch (\Exception $e) {
            print $e->getMessage() . "\n";
            exit(\PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
        }

        $configuration->handlePHPConfiguration();

        $testSuite = $configuration
            ->getTestSuiteConfiguration();

        $this->addToQueueByFile($queue, $testSuite);
        $queue->freeze();
    }

    private function addToQueueByFile(QueueInterface $queue, \PHPUnit_Framework_TestSuite $testSuite)
    {
        foreach($testSuite->getIterator() as $suite) {
            echo $suite->getName();
            $queue->enqueue(InputLine::fromString($suite->getName()));
        }
    }
}