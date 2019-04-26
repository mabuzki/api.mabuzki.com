<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */
    'supportsCredentials' => false,
    'allowedOrigins' => ['*'],
    'allowedOriginsPatterns' => [],
    // 'allowedHeaders' => ['Content-Type', 'Authorization','multipart/form-data'],
    'allowedHeaders' => ['Content-Type', 'Authorization'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => ['Authorization'],
    'maxAge' => 0,

];
