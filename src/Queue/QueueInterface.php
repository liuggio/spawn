<?php

namespace Liuggio\Concurrent\Queue;

interface QueueInterface
{
    /**
     * Adds an element to the queue.
     *
     * @param mixed $value <p>
     *                     The value to enqueue.
     *                     </p>
     */
    public function enqueue($value);

    /**
     * Dequeues a node from the queue.
     *
     * @return mixed|null The value of the dequeued node or null if the queue is empty.
     *
     * @throws \RuntimeException when the data-structure is empty.
     */
    public function dequeue();

    /**
     * Randomizes and return a new QueueInterface.
     *
     * @return static
     */
    public function randomize();

    /**
     * Checks if the queue is freezed.
     *
     * @return bool whether the heap is empty.
     */
    public function isFrozen();

    /**
     * Freeze the queue for future write access.
     *
     * @return bool whether the heap is empty.
     */
    public function freeze();

    /**
     * Count elements of a queue.
     *
     * @return int queue items count as an integer.
     */
    public function count();
}
