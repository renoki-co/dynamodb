<?php

namespace Rennokki\DynamoDb;

interface DynamoDbClientInterface
{
    /**
     * Get the DynamoDb client configuration.
     *
     * @param  string|null  $connection
     * @return \Aws\DynamoDb\DynamoDbClient
     */
    public function getClient($connection = null);

    /**
     * Get the marshaler for DynamoDb.
     *
     * @return \Aws\DynamoDb\Marshaler
     */
    public function getMarshaler();

    /**
     * Get the attribute filter.
     *
     * @return \Rennokki\DynamoDb\EmptyAttributeFilter
     */
    public function getAttributeFilter();
}
