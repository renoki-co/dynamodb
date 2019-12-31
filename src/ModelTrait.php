<?php

namespace Rennokki\DynamoDb;

use Illuminate\Support\Facades\App;

trait ModelTrait
{
    /**
     * Boot the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        $observer = static::getObserverClassName();

        static::observe(new $observer(
            App::make('Rennokki\DynamoDb\DynamoDbClientInterface')
        ));
    }

    /**
     * Get the observer class name.
     *
     * @return string
     */
    public static function getObserverClassName()
    {
        return 'Rennokki\DynamoDb\ModelObserver';
    }

    /**
     * Get the DynamoDb table name.
     *
     * @return string
     */
    public function getDynamoDbTableName()
    {
        return $this->getTable();
    }
}
