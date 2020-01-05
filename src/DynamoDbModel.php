<?php

namespace Rennokki\DynamoDb;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class DynamoDbModel extends Model
{
    /**
     * Always set this to false since DynamoDb does not support incremental Id.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The DynamoDb client interface.
     *
     * @var \Rennokki\DynamoDb\DynamoDbClientInterface
     */
    protected static $dynamoDb;

    /**
     * The DynamoDb marshaler.
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
     * Indexes for the table.
     *
     * [
     *   '<simple_index_name>' => [
     *        'hash' => '<index_key>'
     *   ],
     *   '<composite_index_name>' => [
     *        'hash' => '<index_hash_key>',
     *        'range' => '<index_range_key>'
     *   ],
     * ].
     *
     * @var array
     */
    protected $dynamoDbIndexKeys = [];

    /**
     * Array of your composite key.
     *
     * ['<hash>', '<range>'].
     *
     * @var array
     */
    protected $compositeKey = [];

    /**
     * Default Date format (ISO 8601 Compliant)
     * https://www.php.net/manual/en/class.datetimeinterface.php#datetime.constants.atom.
     *
     * @var string
     */
    protected $dateFormat = DateTime::ATOM;

    /**
     * Initialize the class.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->syncOriginal();
        $this->fill($attributes);
        $this->setupDynamoDb();
    }

    /**
     * Get the DynamoDbClient service that is being used by the models.
     *
     * @return DynamoDbClientInterface
     */
    public static function getDynamoDbClientService()
    {
        return static::$dynamoDb;
    }

    /**
     * Set the DynamoDbClient used by models.
     *
     * @param  DynamoDbClientInterface  $dynamoDb
     * @return void
     */
    public static function setDynamoDbClientService(DynamoDbClientInterface $dynamoDb)
    {
        static::$dynamoDb = $dynamoDb;
    }

    /**
     * Unset the DynamoDbClient service for models.
     *
     * @return void
     */
    public static function unsetDynamoDbClientService()
    {
        static::$dynamoDb = null;
    }

    /**
     * Set up the DynamoDb marshaler and attribute filters.
     *
     * @return void
     */
    protected function setupDynamoDb()
    {
        $this->marshaler = static::$dynamoDb->getMarshaler();
        $this->attributeFilter = static::$dynamoDb->getAttributeFilter();
    }

    /**
     * Create a new DynamoDb Collection instance.
     *
     * @param  array  $models
     * @param  \Rennokki\DynamoDb\ConditionAnalyzer\Index|null  $index
     * @return DynamoDbCollection
     */
    public function newCollection(array $models = [], $index = null)
    {
        return new DynamoDbCollection($models, $index);
    }

    /**
     * Trigger the save action.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $create = ! $this->exists;

        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        if ($create && $this->fireModelEvent('creating') === false) {
            return false;
        }

        if (! $create && $this->fireModelEvent('updating') === false) {
            return false;
        }

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $saved = $this->newQuery()->save();

        if (! $saved) {
            return $saved;
        }

        $this->exists = true;
        $this->wasRecentlyCreated = $create;

        $this->fireModelEvent($create ? 'created' : 'updated', false);
        $this->finishSave($options);

        return $saved;
    }

    /**
     * Saves the model to DynamoDb asynchronously and returns a promise.
     *
     * @param  array  $options
     * @return bool|\GuzzleHttp\Promise\Promise
     */
    public function saveAsync(array $options = [])
    {
        $create = ! $this->exists;

        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        if ($create && $this->fireModelEvent('creating') === false) {
            return false;
        }

        if (! $create && $this->fireModelEvent('updating') === false) {
            return false;
        }

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $savePromise = $this->newQuery()->saveAsync();

        $savePromise->then(function ($result) use ($create, $options) {
            if (Arr::get($result, '@metadata.statusCode') === 200) {
                $this->exists = true;
                $this->wasRecentlyCreated = $create;

                $this->fireModelEvent($create ? 'created' : 'updated', false);
                $this->finishSave($options);
            }
        });

        return $savePromise;
    }

    /**
     * Trigger the update action.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        return $this->fill($attributes)->save();
    }

    /**
     * Trigger the update actions and return a promise.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool|\GuzzleHttp\Promise\Promise
     */
    public function updateAsync(array $attributes = [], array $options = [])
    {
        return $this->fill($attributes)->saveAsync($options);
    }

    /**
     * Create a new record in the table.
     *
     * @param  array  $attributes
     * @return \Rennokki\DynamoDb\DynamoDbModel
     */
    public static function create(array $attributes = [])
    {
        $model = new static;

        $model->fill($attributes)->save();

        return $model;
    }

    /**
     * Trigger the delete action.
     *
     * @return bool|null
     */
    public function delete()
    {
        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }

        if ($this->exists) {
            if ($this->fireModelEvent('deleting') === false) {
                return false;
            }

            $this->exists = false;

            $success = $this->newQuery()->delete();

            if ($success) {
                $this->fireModelEvent('deleted', false);
            }

            return $success;
        }
    }

    /**
     * Trigger the delete action and return a promise.
     *
     * @return bool|\GuzzleHttp\Promise\Promise
     */
    public function deleteAsync()
    {
        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }

        if ($this->exists) {
            if ($this->fireModelEvent('deleting') === false) {
                return false;
            }

            $this->exists = false;

            $deletePromise = $this->newQuery()->deleteAsync();

            $deletePromise->then(function () {
                $this->fireModelEvent('deleted', false);
            });

            return $deletePromise;
        }
    }

    /**
     * Get a list of all of the records.
     *
     * @param  array  $columns
     * @return \Rennokki\DynamoDb\DynamoDbCollection
     */
    public static function all($columns = [])
    {
        $instance = new static;

        return $instance->newQuery()->get($columns);
    }

    /**
     * Refresh the model.
     *
     * @return \Rennokki\DynamoDb\DynamoDbModel
     */
    public function refresh()
    {
        if (! $this->exists) {
            return $this;
        }

        $query = $this->newQuery();

        $refreshed = $query->find($this->getKeys());

        $this->setRawAttributes($refreshed->toArray());

        return $this;
    }

    /**
     * Generate a new query.
     *
     * @return DynamoDbQueryBuilder
     */
    public function newQuery()
    {
        $builder = new DynamoDbQueryBuilder($this);

        foreach ($this->getGlobalScopes() as $identifier => $scope) {
            $builder->withGlobalScope($identifier, $scope);
        }

        return $builder;
    }

    /**
     * Check if the model has a composite key.
     *
     * @return bool
     */
    public function hasCompositeKey(): bool
    {
        return ! empty($this->compositeKey);
    }

    /**
     * Marshal an item.
     *
     * @param  array  $item
     * @return array
     */
    public function marshalItem($item)
    {
        return $this->marshaler->marshalItem($item);
    }

    /**
     * Marshal a value.
     *
     * @param  mixed  $value
     * @return array
     */
    public function marshalValue($value)
    {
        return $this->marshaler->marshalValue($value);
    }

    /**
     * Unmarshal an item.
     *
     * @param  array  $item
     * @return array|\stdClass
     */
    public function unmarshalItem($item)
    {
        return $this->marshaler->unmarshalItem($item);
    }

    /**
     * Set a new id for this model.
     *
     * @param  mixed  $id
     * @return \Rennokki\DynamoDb\DynamoDbModel
     */
    public function setId($id)
    {
        if (! is_array($id)) {
            $this->setAttribute($this->getKeyName(), $id);

            return $this;
        }

        foreach ($id as $keyName => $value) {
            $this->setAttribute($keyName, $value);
        }

        return $this;
    }

    /**
     * Get the DynamoDb client.
     *
     * @return \Aws\DynamoDb\DynamoDbClient
     */
    public function getClient()
    {
        return static::$dynamoDb->getClient($this->connection);
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the value of the model's primary / composite key.
     * Use this if you always want the key values in associative array form.
     *
     * @return array
     *
     * ['id' => 'foo']
     *
     * or
     *
     * ['id' => 'foo', 'id2' => 'bar']
     */
    public function getKeys()
    {
        if ($this->hasCompositeKey()) {
            $key = [];

            foreach ($this->compositeKey as $name) {
                $key[$name] = $this->getAttribute($name);
            }

            return $key;
        }

        $name = $this->getKeyName();

        return [$name => $this->getAttribute($name)];
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the primary/composite key for the model.
     *
     * @return array
     */
    public function getKeyNames()
    {
        return $this->hasCompositeKey() ? $this->compositeKey : [$this->primaryKey];
    }

    /**
     * Get the DynamoDb index keys.
     *
     * @return array
     */
    public function getDynamoDbIndexKeys()
    {
        return $this->dynamoDbIndexKeys;
    }

    /**
     * Set the DynamoDb index keys.
     *
     * @param array $dynamoDbIndexKeys
     * @return void
     */
    public function setDynamoDbIndexKeys($dynamoDbIndexKeys)
    {
        $this->dynamoDbIndexKeys = $dynamoDbIndexKeys;
    }

    /**
     * Get the DynamoDb marshaler.
     *
     * @return \Aws\DynamoDb\Marshaler
     */
    public function getMarshaler()
    {
        return $this->marshaler;
    }

    /**
     * Get the total item count for the table using
     * the describeTable() method from AWS SDK.
     *
     * @return int
     */
    public static function getItemsCount(): int
    {
        $model = new static;
        $describeTable = $model->getClient()->describeTable([
            'TableName' => $model->getTable(),
        ]);

        return $describeTable->get('Table')['ItemCount'];
    }

    /**
     * Remove non-serializable properties when serializing.
     *
     * @return array
     */
    public function __sleep()
    {
        return array_keys(
            Arr::except(get_object_vars($this), ['marshaler', 'attributeFilter'])
        );
    }

    /**
     * When a model is being unserialized, check if it needs to be booted and setup DynamoDB.
     *
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $this->setupDynamoDb();
    }
}
