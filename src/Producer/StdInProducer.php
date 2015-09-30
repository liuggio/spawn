<?php

namespace Liuggio\Spawn\Producer;

use Liuggio\Spawn\Exception\StdInMustBeAValidResourceException;
use Liuggio\Spawn\Queue\QueueInterface;

class StdInProducer implements ProducerInterface
{
    /** @var string */
    private $stdIn;
    /** @var resource|null */
    private $resource = null;

    public function __construct($stdIn = 'php://stdin')
    {
        $this->stdIn = $stdIn;
    }

    /**
     * {@inheritdoc}
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

    private function addLineIfNotEmpty(QueueInterface $queue, $line)
    {
        $line = trim($line);
        if (!empty($line)) {
            $queue->enqueue($line);
        }
    }

    private function assertResourceIsValid()
    {
        if (!$this->resource) {
            throw new StdInMustBeAValidResourceException($this->stdIn);
        }
    }
}
