<?php

namespace Liuggio\Spawn\Producer;

use Liuggio\Spawn\Queue\QueueInterface;

interface ProducerInterface
{
    /**
     * Starts producing new CommandLines into the queue.
     *
     * @param QueueInterface $queue
     */
    public function produce(QueueInterface $queue);
}
