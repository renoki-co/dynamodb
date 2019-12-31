<?php

namespace Rennokki\DynamoDb\ConditionAnalyzer;

class Index
{
    /**
     * The index name.
     *
     * @var string
     */
    public $name;

    /**
     * The hash.
     *
     * @var string
     */
    public $hash;

    /**
     * The range.
     *
     * @var string
     */
    public $range;

    /**
     * Initialize the class.
     *
     * @param  string  $name
     * @param  string  $hash
     * @param  string  $range
     * @return void
     */
    public function __construct($name, $hash, $range)
    {
        $this->name = $name;
        $this->hash = $hash;
        $this->range = $range;
    }

    /**
     * Check if the index is composite.
     *
     * @return bool
     */
    public function isComposite(): bool
    {
        return isset($this->hash) && isset($this->range);
    }

    /**
     * Build the columns.
     *
     * @return array
     */
    public function columns(): array
    {
        $columns = [];

        if ($this->hash) {
            $columns[] = $this->hash;
        }

        if ($this->range) {
            $columns[] = $this->range;
        }

        return $columns;
    }
}
