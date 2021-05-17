<?php

return [
    'timer'  => [
        'count'    => 1,
        'callback' => [
            'onWorkerStart',
        ]
    ],
    'udp'  => [
        'listen'   => 'udp://0.0.0.0:13003',
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
        ]
    ],
    'unix'  => [
        'listen'   => 'unix://' . runtime_path() . '/unix.sock',
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
        ]
    ],
    'http'  => [
        'listen'   => 'http://0.0.0.0:13002',
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
                    'auth'      => 'hsk99',
                    'timeout'   => 2,
                    'database'  => '',
                    'prefix'    => 'redis_session_'
                ],
            ],
        ],
    ],
];
