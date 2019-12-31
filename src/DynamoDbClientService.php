<?php

namespace Rennokki\DynamoDb;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class DynamoDbClientService implements DynamoDbClientInterface
{
    /**
     * A list of clients configurations.
     *
     * @var array
     */
    protected $clients;

    /**
     * The DynamoDb Marshaler.
     *
     * @var \Aws\DynamoDb\Marshaler
     */
    protected $marshaler;

    /**
     * The DynamoDb attribute filter.
     *
     * @var \Rennokki\DynamoDb\EmptyAttributeFilter
     */
    protected $attributeFilter;

    /**
     * Initialize the class.
     *
     * @param  \Aws\DynamoDb\Marshaler  $marshaler
     * @param  EmptyAttributeFilter  $filter
     * @return void
     */
    public function __construct(Marshaler $marshaler, EmptyAttributeFilter $filter)
    {
        $this->marshaler = $marshaler;
        $this->attributeFilter = $filter;
        $this->clients = [];
    }

    /**
     * Get the DynamoDb client configuration.
     *
     * @param  string|null  $connection
     * @return \Aws\DynamoDb\DynamoDbClient
     */
    public function getClient($connection = null)
    {
        $connection = $connection ?: config('dynamodb.default');

        if (isset($this->clients[$connection])) {
            return $this->clients[$connection];
        }

        $config = config("dynamodb.connections.$connection", []);
        $config['version'] = '2012-08-10';
        $config['debug'] = $this->getDebugOptions(Arr::get($config, 'debug'));

        $client = new DynamoDbClient($config);

        $this->clients[$connection] = $client;

        return $client;
    }

    /**
     * Get the marshaler for DynamoDb.
     *
     * @return \Aws\DynamoDb\Marshaler
     */
    public function getMarshaler()
    {
        return $this->marshaler;
    }

    /**
     * Get the attribute filter.
     *
     * @return \Rennokki\DynamoDb\EmptyAttributeFilter
     */
    public function getAttributeFilter()
    {
        return $this->attributeFilter;
    }

    /**
     * Trigger the log if debug is enabled.
     *
     * @param  bool  $debug
     * @return bool|array
     */
    protected function getDebugOptions($debug = false)
    {
        if ($debug === true) {
            $logfn = function ($msg) {
                Log::info($msg);
            };

            return ['logfn' => $logfn];
        }

        return $debug;
    }
}
