<?php

namespace Rennokki\DynamoDb;

class ComparisonOperator
{
    const EQ = 'EQ';
    const GT = 'GT';
    const GE = 'GE';
    const LT = 'LT';
    const LE = 'LE';
    const IN = 'IN';
    const NE = 'NE';
    const BEGINS_WITH = 'BEGINS_WITH';
    const BETWEEN = 'BETWEEN';
    const NOT_CONTAINS = 'NOT_CONTAINS';
    const CONTAINS = 'CONTAINS';
    const NULL = 'NULL';
    const NOT_NULL = 'NOT_NULL';

    /**
     * Get hte operators mapping.
     *
     * @return array
     */
    public static function getOperatorMapping(): array
    {
        return [
            '=' => static::EQ,
            '>' => static::GT,
            '>=' => static::GE,
            '<' => static::LT,
            '<=' => static::LE,
            'in' => static::IN,
            '!=' => static::NE,
            'begins_with' => static::BEGINS_WITH,
            'between' => static::BETWEEN,
            'not_contains' => static::NOT_CONTAINS,
            'contains' => static::CONTAINS,
            'null' => static::NULL,
            'not_null' => static::NOT_NULL,
        ];
    }

    /**
     * Get the list of supported operators (by DynamoDb names)
     *
     * @return array
     */
    public static function getSupportedOperators(): array
    {
        return array_keys(static::getOperatorMapping());
    }

    /**
     * Check if the operator is valid.
     *
     * @return bool
     */
    public static function isValidOperator($operator): bool
    {
        $operator = strtolower($operator);
        $mapping = static::getOperatorMapping();

        return isset($mapping[$operator]);
    }

    /**
     * Get the operator for the DynamoDb operator.
     *
     * @param  string  $operator
     * @return string
     */
    public static function getDynamoDbOperator($operator): string
    {
        $mapping = static::getOperatorMapping();
        $operator = strtolower($operator);

        return $mapping[$operator];
    }

    /**
     * Get a list of query supported operators
     * wether is a range key or not.
     *
     * @param  bool  $isRangeKey
     * @return array
     */
    public static function getQuerySupportedOperators($isRangeKey = false): array
    {
        if ($isRangeKey) {
            return [
                static::EQ,
                static::LE,
                static::LT,
                static::GE,
                static::GT,
                static::BEGINS_WITH,
                static::BETWEEN,
            ];
        }

        return [static::EQ];
    }

    /**
     * Check if the operator is valid for query.
     *
     * @param  string  $operator
     * @param  bool  $isRangeKey
     * @return bool
     */
    public static function isValidQueryOperator($operator, $isRangeKey = false): bool
    {
        $dynamoDbOperator = static::getDynamoDbOperator($operator);

        return static::isValidQueryDynamoDbOperator($dynamoDbOperator, $isRangeKey);
    }

    /**
     * Check if the operator is valid for DynamoDb query.
     *
     * @param  string  $dynamoDbOperator
     * @param  bool  $isRangeKey
     * @return bool
     */
    public static function isValidQueryDynamoDbOperator($dynamoDbOperator, $isRangeKey = false): bool
    {
        return in_array($dynamoDbOperator, static::getQuerySupportedOperators($isRangeKey));
    }

    /**
     * Check if the operator is a DynamoDb operator.
     *
     * @param  string  $op
     * @param  string  $dynamoDbOperator
     * @return bool
     */
    public static function is($op, $dynamoDbOperator): bool
    {
        $mapping = static::getOperatorMapping();

        return $mapping[strtolower($op)] === $dynamoDbOperator;
    }
}
