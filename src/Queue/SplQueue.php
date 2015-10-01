<?php

namespace Liuggio\Spawn\Queue;

use Liuggio\Spawn\Exception\TheQueueMustNotBeFrozenToEnqueueException;

class SplQueue extends \SplQueue implements QueueInterface
{
    /**
     * @var bool
     */
    private $isFrozen = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($queue = null)
    {
        parent::setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO & \SplDoublyLinkedList::IT_MODE_DELETE);
        if (null !== $queue) {
            foreach ($queue as $item) {
                $this->enqueue($item);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue($value)
    {
        $this->assertIsNotFrozen();
        parent::enqueue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        return parent::dequeue();

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function randomize()
    {
        $randomizedArray = [];
        for ($this->rewind(); $this->valid(); $this->next()) {
            $randomizedArray[] = $this->current();
        }

        shuffle($randomizedArray);

        $newQueue = new self();
        foreach ($randomizedArray as $item) {
            $newQueue->enqueue($item);
        }

        return $newQueue;
    }

    /**
     * {@inheritdoc}
     */
    public function isFrozen()
    {
        return $this->isFrozen;
    }

    /**
     * {@inheritdoc}
     */
    public function freeze()
    {
        $this->isFrozen = true;
    }

    /**
     * @throws TheQueueMustNotBeFrozenToEnqueueException
     */
    private function assertIsNotFrozen()
    {
        if ($this->isFrozen()) {
            throw new TheQueueMustNotBeFrozenToEnqueueException();
        }
    }
}
