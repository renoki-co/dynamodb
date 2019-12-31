<?php

namespace Rennokki\DynamoDb\DynamoDb;

use Aws\DynamoDb\DynamoDbClient;
use BadMethodCallException;
use Illuminate\Support\Str;
use Rennokki\DynamoDb\DynamoDbClientInterface;
use Rennokki\DynamoDb\RawDynamoDbQuery;

class QueryBuilder
{
    /**
     * The DynamoDb service.
     *
     * @var DynamoDbClientInterface
     */
    private $service;

    /**
     * Query body to be sent to AWS.
     *
     * @var array
     */
    public $query = [];

    /**
     * Initialize the class.
     *
     * @param  \Rennokki\DynamoDb\DynamoDbClientInterface  $service
     * @return void
     */
    public function __construct(DynamoDbClientInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Hydrate the query.
     *
     * @param  array  $query
     * @return \Rennokki\DynamoDb\DynamoDb\QueryBuilder
     */
    public function hydrate(array $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Set a new attribute name.
     *
     * @param  string  $placeholder
     * @param  string  $name
     * @return \Rennokki\DynamoDb\DynamoDb\QueryBuilder
     */
    public function setExpressionAttributeName($placeholder, $name)
    {
        $this->query['ExpressionAttributeNames'][$placeholder] = $name;

        return $this;
    }

    /**
     * Set a new attribute value.
     *
     * @param  string  $placeholder
     * @param  string  $value
     * @return \Rennokki\DynamoDb\DynamoDb\QueryBuilder
     */
    public function setExpressionAttributeValue($placeholder, $value)
    {
        $this->query['ExpressionAttributeValues'][$placeholder] = $value;

        return $this;
    }

    /**
     * Prepare the query.
     *
     * @param  DynamoDbClient|null  $client
     * @return ExecutableQuery
     */
    public function prepare(DynamoDbClient $client = null)
    {
        $raw = new RawDynamoDbQuery(null, $this->query);

        return new ExecutableQuery($client ?: $this->service->getClient(), $raw->finalize()->query);
    }

    /**
     * Call the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'set')) {
            $key = array_reverse(explode('set', $method, 2))[0];
            $this->query[$key] = current($parameters);

            return $this;
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }
}
