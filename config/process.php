<?php

/**
 * 除下列必填参数外可自定义添加新参数
 * 
 * ******Workerman进程配置参数 (“可选项” 不填写则注释不要留空)******
 * 
 * 监听的协议 ip 及端口 (可选)
 * 'listen'     => '',
 * 进程数 (可选，默认1)
 * 'count'      => 5,
 * 进程运行用户 (可选，默认当前用户)
 * 'user'       => '',
 * 进程运行用户组 (可选，默认当前用户组)
 * 'group'      => '',
 * 当前进程是否支持reload (可选，默认true)
 * 'reloadable' => true,
 * 是否开启reusePort (可选，此选项需要php >=7.0，默认为true)
 * 'reusePort'  => true,
 * 传输协议 (可选，当需要开启ssl时设置为ssl，默认为tcp)
 * 'transport'  => 'tcp',
 * 证书信息 (可选，当transport为是ssl时，需要传递证书路径)
 * 'context'    => [], 
 * 回调
 * 'callback'   => [
 *	    'onWorkerStart',
 *	    'onWorkerReload',
 *	    'onConnect',
 *	    'onMessage',
 *	    'onClose',
 *	    'onError',
 *	    'onBufferFull',
 *	    'onBufferDrain',
 *	    'onWorkerStop'
 *	]
 *
 * ******GatewayWorker进程配置参数 (“可选项” 不填写则注释不要留空)******
 * 
 * 监听的协议 ip 及端口
 * "listen"         => 'tcp://0.0.0.0:1111',
 * 传输协议 (可选，当需要开启ssl时设置为ssl，默认为tcp)
 * 'transport'      => 'tcp',
 * 证书信息 (可选，当transport为是ssl时，需要传递证书路径)
 * 'context'        => [],
 * 进程数量
 * "count"          => 1,
 * 内网通讯IP
 * "lan_ip"         => '127.0.0.1',
 * 内网通讯开始端口
 * "start_port"     => 1200,
 * 心跳间隔
 * "pinginterval"   => 10,
 * 心跳数据
 * "pingdata"       => '{"type":"ping"}',
 * 注册地址
 * "register"       => '127.0.0.1:1300',
 * Business进程数量
 * "business_count" => 1,
 * 回调
 * 'callback'   	=> [
 *	    'onWorkerStart',
 *	    'onConnect',
 *	    'onWebSocketConnect',
 *	    'onMessage',
 *	    'onClose',
 *	    'onWorkerStop'
 *	]
 */

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
];