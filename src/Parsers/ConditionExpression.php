<?php

namespace Rennokki\DynamoDb\Parsers;

use Illuminate\Support\Arr;
use Rennokki\DynamoDb\ComparisonOperator;
use Rennokki\DynamoDb\Facades\DynamoDb;
use Rennokki\DynamoDb\NotSupportedException;

class ConditionExpression
{
    /**
     * Define a mapping for operators
     * in DynamoDB.
     *
     * @var array
     */
    const OPERATORS = [
        ComparisonOperator::EQ => '%s = :%s',
        ComparisonOperator::LE => '%s <= :%s',
        ComparisonOperator::LT => '%s < :%s',
        ComparisonOperator::GE => '%s >= :%s',
        ComparisonOperator::GT => '%s > :%s',
        ComparisonOperator::BEGINS_WITH => 'begins_with(%s, :%s)',
        ComparisonOperator::BETWEEN => '(%s BETWEEN :%s AND :%s)',
        ComparisonOperator::CONTAINS => 'contains(%s, :%s)',
        ComparisonOperator::NOT_CONTAINS => 'NOT contains(%s, :%s)',
        ComparisonOperator::NULL => 'attribute_not_exists(%s)',
        ComparisonOperator::NOT_NULL => 'attribute_exists(%s)',
        ComparisonOperator::NE => '%s <> :%s',
        ComparisonOperator::IN => '%s IN (%s)',
    ];

    /**
     * The values for the condition expression.
     *
     * @var ExpressionAttributeValues
     */
    protected $values;

    /**
     * The attribute names.
     *
     * @var ExpressionAttributeNames
     */
    protected $names;

    /**
     * The placeholder.
     *
     * @var Placeholder
     */
    protected $placeholder;

    /**
     * Initialize the class.
     *
     * @param  Placeholder  $placeholder
     * @param  ExpressionAttributeValues  $values
     * @param  ExpressionAttributeNames  $names
     * @return void
     */
    public function __construct(Placeholder $placeholder, ExpressionAttributeValues $values, ExpressionAttributeNames $names)
    {
        $this->placeholder = $placeholder;
        $this->values = $values;
        $this->names = $names;
    }

    /**
     * Parse the where condition.
     *
     * @param array $where
     *   [
     *     'column' => 'name',
     *     'type' => 'EQ',
     *     'value' => 'foo',
     *     'boolean' => 'and',
     *   ]
     *
     * @return string
     * @throws NotSupportedException
     */
    public function parse($where)
    {
        if (empty($where)) {
            return '';
        }

        $parsed = [];

        foreach ($where as $condition) {
            $boolean = Arr::get($condition, 'boolean');
            $value = Arr::get($condition, 'value');
            $type = Arr::get($condition, 'type');

            $prefix = '';

            if (count($parsed) > 0) {
                $prefix = strtoupper($boolean).' ';
            }

            if ($type === 'Nested') {
                $parsed[] = $prefix.$this->parseNestedCondition($value);
                continue;
            }

            $parsed[] = $prefix.$this->parseCondition(
                Arr::get($condition, 'column'),
                $type,
                $value
            );
        }

        return implode(' ', $parsed);
    }

    /**
     * Reset the values.
     *
     * @return void
     */
    public function reset()
    {
        $this->placeholder->reset();
        $this->names->reset();
        $this->values->reset();
    }

    /**
     * Get a list of all supported operators.
     *
     * @return array
     */
    protected function getSupportedOperators(): array
    {
        return static::OPERATORS;
    }

    /**
     * Parse the nested conditions.
     *
     * @param  array  $conditions
     * @return string
     */
    protected function parseNestedCondition(array $conditions): string
    {
        $conditions = $this->parse($conditions);

        return "({$conditions})";
    }

    /**
     * Parse the condition.
     *
     * @param  string  $name
     * @param  string  $operator
     * @param  mixed  $value
     * @return mixed
     */
    protected function parseCondition($name, $operator, $value)
    {
        $operators = $this->getSupportedOperators();

        if (empty($operators[$operator])) {
            throw new NotSupportedException("$operator is not supported");
        }

        $template = $operators[$operator];

        $this->names->set($name);

        if ($operator === ComparisonOperator::BETWEEN) {
            return $this->parseBetweenCondition($name, $value, $template);
        }

        if ($operator === ComparisonOperator::IN) {
            return $this->parseInCondition($name, $value, $template);
        }

        if ($operator === ComparisonOperator::NULL || $operator === ComparisonOperator::NOT_NULL) {
            return $this->parseNullCondition($name, $template);
        }

        $placeholder = $this->placeholder->next();

        $this->values->set($placeholder, DynamoDb::marshalValue($value));

        return sprintf($template, $this->names->placeholder($name), $placeholder);
    }

    /**
     * Parse the between condition.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @param  mixed  $template
     * @return string
     */
    protected function parseBetweenCondition($name, $value, $template)
    {
        $first = $this->placeholder->next();
        $second = $this->placeholder->next();

        $this->values->set($first, DynamoDb::marshalValue($value[0]));
        $this->values->set($second, DynamoDb::marshalValue($value[1]));

        return sprintf($template, $this->names->placeholder($name), $first, $second);
    }

    /**
     * Parse the whereIn condition.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @param  mixed  $template
     * @return void
     */
    protected function parseInCondition($name, $value, $template)
    {
        $valuePlaceholders = [];

        foreach ($value as $item) {
            $placeholder = $this->placeholder->next();
            $valuePlaceholders[] = ':'.$placeholder;

            $this->values->set($placeholder, DynamoDb::marshalValue($item));
        }

        return sprintf($template, $this->names->placeholder($name), implode(', ', $valuePlaceholders));
    }

    /**
     * Parse the null condition.
     *
     * @param  string  $name
     * @param  mixed  $template
     * @return string
     */
    protected function parseNullCondition($name, $template)
    {
        return sprintf($template, $this->names->placeholder($name));
    }
}
