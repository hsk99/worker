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
     * GatewayWorker进程
     */
	'gateway_worker' => [
		'tcp' => [
			'type'           => ['Register', 'Gateway', 'BusinessWorker'],
			'listen'         => 'tcp://0.0.0.0:9300',
			'count'          => 1,
			'lan_ip'         => '127.0.0.1',
			'start_port'     => 11100,
			'pinginterval'   => 10,
			'pingdata'       => '{"type":"ping"}',
			'register'       => '127.0.0.1:11200',
			'business_count' => 1,
			'callback'       => [
				'onWorkerStart',
				'onConnect',
				'onMessage',
			]
		],
		'websocket' => [
			'type'           => ['Register', 'Gateway', 'BusinessWorker'],
			'listen'         => 'websocket://0.0.0.0:9301',
			'count'          => 1,
			'lan_ip'         => '127.0.0.1',
			'start_port'     => 11300,
			'pinginterval'   => 10,
			'pingdata'       => '{"type":"ping"}',
			'register'       => '127.0.0.1:11400',
			'business_count' => 1,
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
			'listen_port' => 11500,
		]
	],
	/**
     * Channel 进程
     */
	'channel' => [
		'push'  => [
			'listen_ip'   => '127.0.0.1',
			'listen_port' => 11600,
		]
	]
];
