<?php

return [
    'config' => [
        "host"     => '127.0.0.1',
        "port"     => '6379',
        "password" => 'hsk99'
    ],
    'client' => [
        'ssl'  => [
            'listen'    => 'tcp://127.0.0.1:9300',
            'callback'    => [
                'onConnect',
                'onMessage',
                'onClose',
                'onError',
                'onBufferFull',
                'onBufferDrain'
            ],
        ]
    ]
];
