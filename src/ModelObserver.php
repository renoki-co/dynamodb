<?php

namespace Rennokki\DynamoDb;

use Exception;
use Illuminate\Support\Facades\Log;

class ModelObserver
{
    /**
     * The DynamoDb client.
     *
     * @var \Aws\DynamoDb\DynamoDbClient
     */
    protected $dynamoDbClient;

    /**
     * Thre DynamoDb marshaler.
     *
     * @var \Aws\DynamoDb\Marshaler
     */
    protected $marshaler;

    /**
     * The attribute filter.
     *
     * @var \Rennokki\DynamoDb\EmptyAttributeFilter
     */
    protected $attributeFilter;

    /**
     * Initialize the class.
     *
     * @param  DynamoDbClientInterface  $dynamoDb
     * @return void
     */
    public function __construct(DynamoDbClientInterface $dynamoDb)
    {
        $this->dynamoDbClient = $dynamoDb->getClient();
        $this->marshaler = $dynamoDb->getMarshaler();
        $this->attributeFilter = $dynamoDb->getAttributeFilter();
    }

    /**
     * Trigger the DynamoDb query to save the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    private function saveToDynamoDb($model)
    {
        $attrs = $model->attributesToArray();

        try {
            $this->dynamoDbClient->putItem([
                'TableName' => $model->getDynamoDbTableName(),
                'Item' => $this->marshaler->marshalItem($attrs),
            ]);
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * Trigger the DynamoDb query to delete the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    private function deleteFromDynamoDb($model)
    {
        $key = [$model->getKeyName() => $model->getKey()];

        try {
            $this->dynamoDbClient->deleteItem([
                'TableName' => $model->getDynamoDbTableName(),
                'Key' => $this->marshaler->marshalItem($key),
            ]);
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * Handle the Model "created" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function created($model)
    {
        $this->saveToDynamoDb($model);
    }

    /**
     * Handle the Model "updated" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function updated($model)
    {
        $this->saveToDynamoDb($model);
    }

    /**
     * Handle the Model "deleted" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function deleted($model)
    {
        $this->deleteFromDynamoDb($model);
    }

    /**
     * Handle the \Illuminate\Database\Eloquent\Model "restored" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function restored($model)
    {
        $this->saveToDynamoDb($model);
    }

    /**
     * Handle the \Illuminate\Database\Eloquent\Model "force deleted" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function forceDeleted($model)
    {
        $this->deleted($model);
    }
}
