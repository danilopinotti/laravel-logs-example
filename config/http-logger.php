<?php

return [
    'enable' => env('HTTP_LOGGER_ENABLE', true),

    'log_channel' => env('HTTP_LOGGER_LOG_CHANNEL', 'stderr'),

    'log_level' => env('HTTP_LOGGER_LOG_LEVEL', 'info'),

    'hidden_headers' => [
        'cookie',
        'x-csrf-token',
        'x-xsrf-token',
        'authorization',
        'access_token',
        'set-cookie',
    ],

    'hidden_parameters' => [
        '_token',
        'password',
        'password_confirmation',
        'api_id',
        'api_key',
        'access_token',
        'senha',
        'token',
    ],

    'only_paths' => [
        //
    ],

    'except_paths' => [
        '/telescope*',
        '/horizon*',
    ],
];
