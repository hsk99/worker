<?php

return [
    'register' => [
        'type'            => ['Register'],
        'registerAddress' => '127.0.0.1:13010',
        'reloadable'      => true,
        'secretKey'       => 'hsk99',
    ],
    'event' => [
        'type'                    => ['BusinessWorker'],
        'registerAddress'         => '127.0.0.1:13010',
        'secretKey'               => 'hsk99',
        'businessCount'           => 1,
        'callback'                => [
            'onWorkerStart',
            'onConnect',
            'onWebSocketConnect',
            'onMessage',
            'onClose',
            'onWorkerStop'
        ]
    ],
    'websocket' => [
        'type'                   => ['Gateway'],
        'listen'                 => 'websocket://0.0.0.0:13000',
        'gatewayCount'           => 1,
        'lanIp'                  => '127.0.0.1',
        'startPort'              => 13020,
        'pingInterval'           => 10,
        'pingNotResponseLimit'   => 2,
        'pingData'               => '{"type":"ping"}',
        'registerAddress'        => '127.0.0.1:13010',
        'secretKey'              => 'hsk99',
    ],
    'tcp' => [
        'type'                   => ['Gateway'],
        'listen'                 => 'tcp://0.0.0.0:13001',
        'gatewayCount'           => 1,
        'lanIp'                  => '127.0.0.1',
        'startPort'              => 13030,
        'pingInterval'           => 10,
        'pingNotResponseLimit'   => 2,
        'pingData'               => '{"type":"ping"}',
        'registerAddress'        => '127.0.0.1:13010',
        'secretKey'              => 'hsk99',
    ]
];
