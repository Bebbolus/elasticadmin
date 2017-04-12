<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Custom Elasticsearch Client Configuration
    |--------------------------------------------------------------------------
    |
    | This array will be passed to the Elasticsearch client.
    | See configuration options here:
    |
    | http://www.elasticsearch.org/guide/en/elasticsearch/client/php-api/current/_configuration.html
    */

    'config' => [
        'hosts'     => [env('ELS_SERVER','localhost')],
        'retries'   => 1,
    ],

    'max_result' => env('ELS_MAX_RESULT',10000000),

    /*
    |--------------------------------------------------------------------------
    | Default Index Name
    |--------------------------------------------------------------------------
    |
    | This is the index name that Elasticquent will use for all
    | Elasticquent models.
    */

    'default_index' => env('ELS_INDEX','index'),
    'user_index' => env('ELS_INDEX_USER','index-user'),

);
