<?php

namespace Rennokki\DynamoDb\Parsers;

use Illuminate\Support\Arr;
use Rennokki\DynamoDb\ComparisonOperator;

class KeyConditionExpression extends ConditionExpression
{
    /**
     * Get the list of supported operators in key conditions.
     *
     * @return array
     */
    protected function getSupportedOperators(): array
    {
        return Arr::only(static::OPERATORS, [
            ComparisonOperator::EQ,
            ComparisonOperator::LE,
            ComparisonOperator::LT,
            ComparisonOperator::GE,
            ComparisonOperator::GT,
            ComparisonOperator::BEGINS_WITH,
            ComparisonOperator::BETWEEN,
        ]);
    }
}
