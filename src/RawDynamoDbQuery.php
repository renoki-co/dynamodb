<?php

namespace Rennokki\DynamoDb;

class RawDynamoDbQuery implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * 'Scan', 'Query', etc.
     *
     * @var string
     */
    public $op;

    /**
     * The query body being sent to AWS.
     *
     * @var array
     */
    public $query;

    /**
     * Initialize the class.
     *
     * @param  string  $op
     * @param  array  $query
     * @return void
     */
    public function __construct($op, $query)
    {
        $this->op = $op;
        $this->query = $query;
    }

    /**
     * Perform any final clean up.
     * Remove any empty values to avoid errors.
     *
     * @return \Rennokki\DynamoDb\RawDynamoDbQuery
     */
    public function finalize()
    {
        $this->query = array_filter($this->query, function ($value) {
            return ! empty($value) || is_bool($value) || is_numeric($value);
        });

        return $this;
    }

    /**
     * Whether a offset exists.
     * http://php.net/manual/en/arrayaccess.offsetexists.php.
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->internal()[$offset]);
    }

    /**
     * Offset to retrieve.
     * http://php.net/manual/en/arrayaccess.offsetget.php.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->internal()[$offset];
    }

    /**
     * Offset to set.
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->internal()[$offset] = $value;
    }

    /**
     * Offset to unset.
     * http://php.net/manual/en/arrayaccess.offsetunset.php.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->internal()[$offset]);
    }

    /**
     * Retrieve an external iterator.
     * http://php.net/manual/en/iteratoraggregate.getiterator.php.
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayObject($this->internal());
    }

    /**
     * Count elements of an object.
     * http://php.net/manual/en/countable.count.php.
     *
     * @return int
     */
    public function count()
    {
        return count($this->internal());
    }

    /**
     * For backward compatibility,
     * previously we use array to represent the raw query.
     *
     * @return array
     */
    private function internal(): array
    {
        return [$this->op, $this->query];
    }
}
