<?php

namespace Rennokki\DynamoDb\Parsers;

class ExpressionAttributeValues
{
    /**
     * The mapping for the values.
     *
     * @var array
     */
    protected $mapping;

    /**
     * The prefix name of the value.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Initialize the class.
     *
     * @param  string  $prefix
     */
    public function __construct($prefix = ':')
    {
        $this->reset();

        $this->prefix = $prefix;
    }

    /**
     * Set a value for the attribute.
     *
     * @param  string  $placeholder
     * @param  mixed  $value
     * @return void
     */
    public function set($placeholder, $value)
    {
        $this->mapping["{$this->prefix}{$placeholder}"] = $value;
    }

    /**
     * Get the value for a placeholder.
     *
     * @param  string  $placeholder
     * @return mixed
     */
    public function get($placeholder)
    {
        return $this->mapping[$placeholder];
    }

    /**
     * Get all the mappings.
     *
     * @return array|null
     */
    public function all()
    {
        return $this->mapping;
    }

    /**
     * Get the placeholders.
     *
     * @return array
     */
    public function placeholders(): array
    {
        return array_keys($this->mapping);
    }

    /**
     * Reset the mappings.
     *
     * @return \Rennokki\DynamoDb\Parsers\ExpressionAttributeValues
     */
    public function reset()
    {
        $this->mapping = [];

        return $this;
    }
}
