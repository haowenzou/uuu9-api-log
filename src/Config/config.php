<?php

return [
    //包含的header
    'include_hdrs' => ['Content-Type'],
    //剔除的字段
    'exclude_fields' => ['password', 'token'],
    //服务名称
    'server_name' => env('APP_ROUTE_PREFIX', ''),
    //sls
    'sls' => [
        'endpoint' => env('SLS_API_LOG_ENDPOINT', ''),
        'accessKey' => env('SLS_API_LOG_ACCESS_KEY', ''),
        'secretKey' => env('SLS_API_SECRET_KEY', ''),
        'project' => env('SLS_API_LOG_PROJECT', ''),
        'logStore' => env('SLS_API_LOG_LOG_STORE', ''),
        'topic' => env('SLS_API_LOG_TOPIC', ''),
        'curlTimeOutMs' => env('SLS_API_LOG_CURL_TIME_OUT_MS', 100),
    ]
];
