<?php

namespace Rennokki\DynamoDb\Parsers;

class Placeholder
{
    /**
     * The counter for the attribute names.
     *
     * @var int
     */
    private $counter;

    /**
     * Initialize the class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Increment the value number.
     *
     * @return string
     */
    public function next(): string
    {
        $this->counter += 1;

        return "a{$this->counter}";
    }

    /**
     * Reset the counter.
     *
     * @return \Rennokki\Parsers\Placeholder
     */
    public function reset()
    {
        $this->counter = 0;

        return $this;
    }
}
