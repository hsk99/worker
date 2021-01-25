<?php

return [
    'tcp' => [
        'type'           => ['Register', 'Gateway', 'BusinessWorker'],
        'listen'         => 'tcp://0.0.0.0:9300',
        'count'          => 1,
        'lan_ip'         => '127.0.0.1',
        'start_port'     => 11100,
        'pinginterval'   => 50,
        'pingdata'       => '{"type":"ping"}',
        'register'       => '127.0.0.1:11200',
        'business_count' => 1,
        'callback'       => [
            'onWorkerStart',
            'onConnect',
            'onWebSocketConnect',
            'onMessage',
            'onClose',
            'onWorkerStop'
        ]
    ],
    'websocket' => [
        'type'           => ['Register', 'Gateway', 'BusinessWorker'],
        'listen'         => 'websocket://0.0.0.0:9301',
        'count'          => 1,
        'lan_ip'         => '127.0.0.1',
        'start_port'     => 11300,
        'pinginterval'   => 50,
        'pingdata'       => '{"type":"ping"}',
        'register'       => '127.0.0.1:11400',
        'business_count' => 1,
        'callback'       => [
            'onWorkerStart',
            'onConnect',
            'onWebSocketConnect',
            'onMessage',
            'onClose',
            'onWorkerStop'
        ]
    ]
];
