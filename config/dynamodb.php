<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default DynamoDb Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the DynamoDb connections below you wish
    | to use as your default connection for all DynamoDb work.
    */
    'default' => env('DYNAMODB_CONNECTION', 'aws'),

    /*
    |--------------------------------------------------------------------------
    | DynamoDb Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the DynamoDb connections setup for your application.
    |
    | Most of the connection's config will be fed directly to AwsClient
    | constructor http://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.AwsClient.html#___construct
    */
    'connections' => [
        'aws' => [
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID', ''),
                'secret' => env('AWS_SECRET_ACCESS_KEY', ''),

                // If using as an assumed IAM role, you can also use the `token` parameter
                'token' => env('AWS_DYNAMODB_SESSION_TOKEN', ''),
            ],

            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),

            // If true, it will use Laravel Log.
            // For advanced options, see http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
            'debug' => env('AWS_DYNAMODB_DEBUG', false),
        ],

        'aws_iam_role' => [
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),

            // If true, it will use Laravel Log.
            // For advanced options, see http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
            'debug' => env('AWS_DYNAMODB_DEBUG', false),
        ],

        'local' => [
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID', 'local'),
                'secret' => env('AWS_SECRET_ACCESS_KEY', 'secret'),
            ],

            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),

            // see http://docs.aws.amazon.com/amazondynamodb/latest/developerguide/Tools.DynamoDBLocal.html
            'endpoint' => env('AWS_DYNAMODB_LOCAL_ENDPOINT'),

            // If true, it will use Laravel Log.
            // For advanced options, see http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
            'debug' => true,
        ],

        'test' => [
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID', 'local'),
                'secret' => env('AWS_SECRET_ACCESS_KEY', 'secret'),
            ],

            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),

            // see http://docs.aws.amazon.com/amazondynamodb/latest/developerguide/Tools.DynamoDBLocal.html
            'endpoint' => env('AWS_DYNAMODB_LOCAL_ENDPOINT'),

            // If true, it will use Laravel Log.
            // For advanced options, see http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
            'debug' => true,
        ],
    ],
];
