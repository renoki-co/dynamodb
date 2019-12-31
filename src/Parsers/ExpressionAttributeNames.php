<?php

namespace Rennokki\DynamoDb\Parsers;

class ExpressionAttributeNames
{
    /**
     * The mapping for names.
     *
     * @var array
     */
    protected $mapping;

    /**
     * The nested attribute names.
     *
     * @var array
     */
    protected $nested;

    /**
     * The prefix for attribute names.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Initialize the class.
     *
     * @param  string  $prefix
     * @return void
     */
    public function __construct($prefix = '#')
    {
        $this->reset();

        $this->prefix = $prefix;
    }

    /**
     * Set the attribute name.
     *
     * @param  string  $name
     * @return void
     */
    public function set($name)
    {
        if ($this->isNested($name)) {
            $this->nested[] = $name;

            return;
        }

        $this->mapping["{$this->prefix}{$name}"] = $name;
    }

    /**
     * Get the attribute name.
     *
     * @param  string  $placeholder
     * @return mixed
     */
    public function get($placeholder)
    {
        return $this->mapping[$placeholder];
    }

    /**
     * Set the placeholder.
     *
     * @param  string  $name
     * @return string
     */
    public function placeholder($name): string
    {
        $placeholder = "{$this->prefix}{$name}";

        if (isset($this->mapping[$placeholder])) {
            return $placeholder;
        }

        return $name;
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
    public function placeholders()
    {
        return array_merge(array_keys($this->mapping), $this->nested);
    }

    /**
     * Reset the values.
     *
     * @return \Rennokki\DynamoDb\Parsers\ExpressionAttributeNames
     */
    public function reset()
    {
        $this->mapping = [];
        $this->nested = [];

        return $this;
    }

    /**
     * Check if the attribute name is nested.
     *
     * @param  string  $name
     * @return bool
     */
    private function isNested($name)
    {
        return strpos($name, '.') !== false || (strpos($name, '[') !== false && strpos($name, ']') !== false);
    }
}
