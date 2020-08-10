<?php

return [
	/**
	 * Workerman进程
	 */
    'workerman' => [
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
	            'onMessage',
			]
	    ],
    ],
    /**
     * GatewayWorker进程
     */
    'gateway_worker' => [
    	'tcp' => [
			"listen"         => 'tcp://0.0.0.0:9300',
			"count"          => 1,
			"lan_ip"         => '127.0.0.1',
			"start_port"     => 11100,
			"pinginterval"   => 10,
			"pingdata"       => '{"type":"ping"}',
			"register"       => '127.0.0.1:11200',
			"business_count" => 1,
			'callback'       => [
				'onWorkerStart',
	            'onConnect',
	            'onMessage',
			]
	    ],
	    'websocket' => [
			"listen"         => 'websocket://0.0.0.0:9301',
			"count"          => 1,
			"lan_ip"         => '127.0.0.1',
			"start_port"     => 11300,
			"pinginterval"   => 10,
			"pingdata"       => '{"type":"ping"}',
			"register"       => '127.0.0.1:11400',
			"business_count" => 1,
			'callback'       => [
				'onWorkerStart',
	            'onWebSocketConnect',
	            'onMessage',
			]
	    ]
    ],
    /**
     * GlobalData 进程
     */
    'global_data' => [
    	'config'  => [
			'listen_ip'   => '127.0.0.1',
			'listen_port' => 12500,
	    ]
    ],
    /**
     * Channel 进程
     */
    'channel' => [
    	'push'  => [
			'listen_ip'   => '127.0.0.1',
			'listen_port' => 12600,
	    ]
    ]
];