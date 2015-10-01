<?php

namespace Liuggio\Spawn\Producer;

use Liuggio\Spawn\Exception\StdInMustBeAValidResourceException;
use Liuggio\Spawn\Queue\QueueInterface;

class StdInProducer implements ProducerInterface
{
    /**
     * @var string
     */
    private $stdIn;

    /**
     * @var resource|null
     */
    private $resource = null;

    /**
     * @param string $stdIn
     */
    public function __construct($stdIn = 'php://stdin')
    {
        $this->stdIn = (string) $stdIn;
    }

    /**
     * @param QueueInterface $queue
     */
    public function produce(QueueInterface $queue)
    {
        $this->resource = @fopen($this->stdIn, 'r');
        $this->assertResourceIsValid();

        while (false !== ($line = fgets($this->resource))) {
            $this->addLineIfNotEmpty($queue, $line);
        }
        $queue->freeze();
    }

    public function __destruct()
    {
        if (null !== $this->resource) {
            @fclose($this->resource);
        }
    }

    /**
     * @param QueueInterface $queue
     * @param string         $line
     */
    private function addLineIfNotEmpty(QueueInterface $queue, $line)
    {
        if ($line = trim($line)) {
            $queue->enqueue($line);
        }
    }

    /**
     * @throws StdInMustBeAValidResourceException
     */
    private function assertResourceIsValid()
    {
        if (!$this->resource) {
            throw new StdInMustBeAValidResourceException($this->stdIn);
        }
    }
}
