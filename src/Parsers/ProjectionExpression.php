<?php

namespace Rennokki\DynamoDb\Parsers;

class ProjectionExpression
{
    protected $names;

    /**
     * Initialize the class.
     *
     * @param  ExpressionAttributeNames  $names
     * @return void
     */
    public function __construct(ExpressionAttributeNames $names)
    {
        $this->names = $names;
    }

    /**
     * Parse the columns for the projection.
     *
     * @param  array  $columns
     * @return string
     */
    public function parse(array $columns): string
    {
        foreach ($columns as $column) {
            $this->names->set($column);
        }

        return implode(', ', $this->names->placeholders());
    }
}
