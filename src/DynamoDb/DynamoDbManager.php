<?php

namespace Rennokki\DynamoDb\DynamoDb;

use Rennokki\DynamoDb\DynamoDbClientInterface;

class DynamoDbManager
{
    /**
     * The service.
     *
     * @var DynamoDbClientInterface
     */
    private $service;

    /**
     * The Marshaler.
     *
     * @var \Aws\DynamoDb\Marshaler
     */
    public $marshaler;

    /**
     * Initialize the class.
     *
     * @param  DynamoDbClientInterface  $service
     * @return void
     */
    public function __construct(DynamoDbClientInterface $service)
    {
        $this->service = $service;
        $this->marshaler = $service->getMarshaler();
    }

    /**
     * Marshal the item.
     *
     * @param  array  $item
     * @return array
     */
    public function marshalItem($item): array
    {
        return $this->marshaler->marshalItem($item);
    }

    /**
     * Marshal the value.
     *
     * @param  mixed  $value
     * @return array
     */
    public function marshalValue($value): array
    {
        return $this->marshaler->marshalValue($value);
    }

    /**
     * Unmarshal an item.
     *
     * @param  array  $item
     * @return array
     */
    public function unmarshalItem($item): array
    {
        return $this->marshaler->unmarshalItem($item);
    }

    /**
     * Unmarshal a value.
     *
     * @param  mixed  $value
     * @return array|string
     */
    public function unmarshalValue($value)
    {
        return $this->marshaler->unmarshalValue($value);
    }

    /**
     * Get the client.
     *
     * @param  string|null  $connection
     * @return \Aws\DynamoDb\DynamoDbClient
     */
    public function client($connection = null)
    {
        return $this->service->getClient($connection);
    }

    /**
     * Get the instance of a new query builder.
     *
     * @return QueryBuilder
     */
    public function newQuery()
    {
        return new QueryBuilder($this->service);
    }

    /**
     * Set the table name.
     *
     * @param  string  $table
     * @return QueryBuilder
     */
    public function table($table)
    {
        return $this->newQuery()->setTableName($table);
    }
}
