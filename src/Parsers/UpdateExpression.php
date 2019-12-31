<?php

namespace Rennokki\DynamoDb\Parsers;

class UpdateExpression
{
    /**
     * The expression attribute names.
     *
     * @var ExpressionAttributeNames
     */
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
     * Reset the names.
     *
     * @return void
     */
    public function reset()
    {
        $this->names->reset();
    }

    /**
     * Remove the given attributes.
     *
     * @param  array  $attributes
     * @return string
     */
    public function remove(array $attributes): string
    {
        foreach ($attributes as $attribute) {
            $this->names->set($attribute);
        }

        return 'REMOVE '.implode(', ', $this->names->placeholders());
    }
}
