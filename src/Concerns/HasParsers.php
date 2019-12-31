<?php

namespace Rennokki\DynamoDb\Concerns;

use Rennokki\DynamoDb\Parsers\ExpressionAttributeNames;
use Rennokki\DynamoDb\Parsers\ExpressionAttributeValues;
use Rennokki\DynamoDb\Parsers\FilterExpression;
use Rennokki\DynamoDb\Parsers\KeyConditionExpression;
use Rennokki\DynamoDb\Parsers\Placeholder;
use Rennokki\DynamoDb\Parsers\ProjectionExpression;
use Rennokki\DynamoDb\Parsers\UpdateExpression;

trait HasParsers
{
    /**
     * The filter expression.
     *
     * @var FilterExpression
     */
    protected $filterExpression;

    /**
     * The key condition.
     *
     * @var KeyConditionExpression
     */
    protected $keyConditionExpression;

    /**
     * The expression for projection.
     *
     * @var ProjectionExpression
     */
    protected $projectionExpression;

    /**
     * The update expression.
     *
     * @var UpdateExpression
     */
    protected $updateExpression;

    /**
     * Attribute names of the expression.
     *
     * @var ExpressionAttributeNames
     */
    protected $expressionAttributeNames;

    /**
     * Expression attribute values.
     *
     * @var ExpressionAttributeValues
     */
    protected $expressionAttributeValues;

    /**
     * The placeholder for the expression.
     *
     * @var Placeholder
     */
    protected $placeholder;

    /**
     * Setup expressions.
     *
     * @return void
     */
    public function setupExpressions(): void
    {
        $this->placeholder = new Placeholder;
        $this->expressionAttributeNames = new ExpressionAttributeNames;
        $this->expressionAttributeValues = new ExpressionAttributeValues;

        $this->keyConditionExpression = new KeyConditionExpression(
            $this->placeholder,
            $this->expressionAttributeValues,
            $this->expressionAttributeNames
        );

        $this->filterExpression = new FilterExpression(
            $this->placeholder,
            $this->expressionAttributeValues,
            $this->expressionAttributeNames
        );

        $this->projectionExpression = new ProjectionExpression($this->expressionAttributeNames);
        $this->updateExpression = new UpdateExpression($this->expressionAttributeNames);
    }

    /**
     * Reset the expressions.
     *
     * @return void
     */
    public function resetExpressions(): void
    {
        $this->filterExpression->reset();
        $this->keyConditionExpression->reset();
        $this->updateExpression->reset();
    }
}
