<?php

namespace Rennokki\DynamoDb;

use Aws\DynamoDb\Marshaler;
use Illuminate\Support\ServiceProvider;
use Rennokki\DynamoDb\DynamoDb\DynamoDbManager;

class DynamoDbServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DynamoDbModel::setDynamoDbClientService(
            $this->app->make(DynamoDbClientInterface::class)
        );

        $this->publishes([
            __DIR__.'/../config/dynamodb.php' => config_path('dynamodb.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/dynamodb.php', 'dynamodb'
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $marshalerOptions = [
            'nullify_invalid' => true,
        ];

        $this->app->singleton(DynamoDbClientInterface::class, function () use ($marshalerOptions) {
            return new DynamoDbClientService(
                new Marshaler($marshalerOptions), new EmptyAttributeFilter
            );
        });

        $this->app->singleton('dynamodb', function () {
            return new DynamoDbManager(
                $this->app->make(DynamoDbClientInterface::class)
            );
        });
    }
}
