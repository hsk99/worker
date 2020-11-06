<?php

return [
	/**
	 * Workerman
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
	],
	/**
     * GatewayWorker
     */
	'gateway_worker' => [
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
	],
	/**
     * GlobalData
     */
	'global_data' => [
		'config'  => [
			'listen_ip'   => '127.0.0.1',
			'listen_port' => 11500,
		]
	],
	/**
     * Channel
     */
	'channel' => [
		'push'  => [
			'listen_ip'   => '127.0.0.1',
			'listen_port' => 11600,
		]
	]
];
