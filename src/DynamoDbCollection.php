<?php

namespace Rennokki\DynamoDb;

use Illuminate\Database\Eloquent\Collection;
use Rennokki\DynamoDb\ConditionAnalyzer\Index;

class DynamoDbCollection extends Collection
{
    /**
     * The condition analyzer for index.
     *
     * @var \Rennokki\DynamoDb\ConditionAnalyzer\Index|null
     */
    private $conditionIndex = null;

    /**
     * Initialize the class.
     *
     * @param  array  $items
     * @param  \Rennokki\DynamoDb\ConditionAnalyzer\Index  $conditionIndex
     * @return void
     */
    public function __construct(array $items = [], Index $conditionIndex = null)
    {
        parent::__construct($items);

        $this->conditionIndex = $conditionIndex;
    }

    /**
     * Get the last key. Used for limit/offset queries.
     *
     * @return null|string
     */
    public function lastKey()
    {
        $after = $this->last();

        if (empty($after)) {
            return;
        }

        $afterKey = $after->getKeys();

        $attributes = $this->conditionIndex ? $this->conditionIndex->columns() : [];

        foreach ($attributes as $attribute) {
            $afterKey[$attribute] = $after->getAttribute($attribute);
        }

        return $afterKey;
    }
}
