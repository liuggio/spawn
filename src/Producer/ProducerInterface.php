<?php

namespace Liuggio\Concurrent\Producer;

use Liuggio\Concurrent\Queue\QueueInterface;

interface ProducerInterface
{
    /**
     * Starts producing new CommandLines into the queue.
     *
     * @param QueueInterface $queue
     */
    public function produce(QueueInterface $queue);
}
