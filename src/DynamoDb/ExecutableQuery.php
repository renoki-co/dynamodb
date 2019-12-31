<?php

namespace Rennokki\DynamoDb\DynamoDb;

use Aws\DynamoDb\DynamoDbClient;

class ExecutableQuery
{
    /**
     * The DynamoDb client.
     *
     * @var DynamoDbClient
     */
    private $client;

    /**
     * The query.
     *
     * @var array
     */
    public $query;

    /**
     * Initialize the class.
     *
     * @param  \Aws\DynamoDb\DynamoDbClient  $client
     * @param  array $query
     * @return void
     */
    public function __construct(DynamoDbClient $client, array $query)
    {
        $this->client = $client;
        $this->query = $query;
    }

    /**
     * Intecept the call class.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->client->{$method}($this->query);
    }
}
