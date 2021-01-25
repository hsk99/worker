<?php

return [
    'timer'  => [
        'count'    => 5,
        'callback' => [
            'onWorkerStart',
        ]
    ],
    'http'  => [
        'listen'   => 'http://0.0.0.0:9302',
        'count'    => 1,
        'callback' => [
            'onWorkerStart',
            'onWorkerReload',
            'onConnect',
            'onMessage',
            'onClose',
            'onError',
            'onBufferFull',
            'onBufferDrain',
            'onWorkerStop'
        ],
        'session'  => [
            'session_name' => 'PHPSID',
            'type'         => 'file',
            'config'       => [
                'file' => [
                    'save_path' => runtime_path() . DS . 'sessions',
                ],
                'redis' => [
                    'host'      => '127.0.0.1',
                    'port'      => 6379,
                    'auth'      => '',
                    'timeout'   => 2,
                    'database'  => '',
                    'prefix'    => 'redis_session_'
                ],
            ],
        ],
    ],
];
