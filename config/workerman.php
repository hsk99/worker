<?php

return [
    'timer'  => [
        // 'handler'   => app\process\Timer::class,
        'count'     => 1,
        'bootstrap' => NULL
    ],
    'tcp'  => [
        // 'handler'   => app\process\Tcp::class,
        'listen'    => 'tcp://0.0.0.0:13001',
        'count'     => 1,
        'bootstrap' => NULL
    ],
    'udp'  => [
        // 'handler'   => app\process\Udp::class,
        'listen'    => 'udp://0.0.0.0:13002',
        'count'     => 1,
        'bootstrap' => NULL
    ],
    'http'  => [
        // 'handler'   => app\process\Http::class,
        'listen'    => 'http://0.0.0.0:13003',
        'count'     => 1,
        'bootstrap' => NULL
    ],
    'unix'  => [
        // 'handler'   => app\process\Unix::class,
        'listen'    => 'unix://' . runtime_path() . '/unix.sock',
        'count'     => 1,
        'bootstrap' => NULL
    ],
];
