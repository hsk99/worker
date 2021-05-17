<?php

ini_set('display_errors', 'on');

if (strpos(strtolower(PHP_OS), 'win') === 0) {
    exit("start.php not support windows\n");
}

require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Protocols\Http;
use Workerman\Protocols\Http\Session;
use Workerman\Connection\TcpConnection;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\AsyncUdpConnection;
use Workerman\Protocols\Http\Session\FileSessionHandler;
use Workerman\Protocols\Http\Session\RedisSessionHandler;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use GatewayWorker\BusinessWorker;
use support\bootstrap\Log;
use support\bootstrap\Config;
use support\bootstrap\CreateFile;

load_files(protocols_path());
load_files(callback_path());
load_files(bootstrap_path());
load_files(extend_path());
Config::load(config_path());

$app = (array)config('app', []);

date_default_timezone_set($app['defaultTimezone'] ?? 'Asia/Shanghai');

if (!is_dir(runtime_path())) {
    mkdir(runtime_path(), 0777, true);
}
Worker::$logFile                      = $app['logFile'] ?? runtime_path() . '/workerman.log';
Worker::$pidFile                      = $app['pidFile'] ?? runtime_path() . '/workerman.pid';
Worker::$stdoutFile                   = $app['stdoutFile'] ?? runtime_path() . '/stdout.log';
TcpConnection::$defaultMaxPackageSize = $app['defaultMaxPackageSize'] ?? 1024000;

Worker::$onMasterReload = function () {
    Config::reload(config_path());
};

$workerman_process      = (array)config('workerman', []);
$gateway_worker_process = (array)config('gateway_worker', []);
$global_data_process    = (array)config('global_data', []);
$channel_process        = (array)config('channel', []);
$async_process          = (array)config('async', []);

if (!empty($workerman_process) && !empty($gateway_worker_process)) {
    $worker_names = array_merge(array_keys($workerman_process), array_keys($gateway_worker_process));
    if (count($worker_names) != count(array_unique($worker_names))) {
        throw new Exception("There are duplicates in the process names of WorkerMan and GatewayWorker");
    }
}

if (!empty($workerman_process)) {
    foreach ($workerman_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $worker       = new Worker($config['listen'] ?? null, $config['context'] ?? []);
        $worker->name = $process_name;

        $property_map = [
            'count',
            'user',
            'group',
            'reloadable',
            'reusePort',
            'transport',
        ];
        foreach ($property_map as $property) {
            if (isset($config[$property])) {
                $worker->$property = $config[$property];
            }
        }

        $worker->config = $config;
        $worker->onWorkerStart = function ($worker) {
            Log::start($worker);

            foreach (config('autoload.files', []) as $file) {
                include_once $file;
            }

            if (in_array($worker->protocol, ["\Workerman\Protocols\Http", "Workerman\Protocols\Http"])) {
                $session      = $worker->config['session'] ?? [];
                $type         = $session['type'] ?? 'file';
                $session_name = $session['session_name'] ?? 'PHPSID';
                $config       = $session['config'][$type] ?? ['save_path' => runtime_path() . DS . 'sessions'];

                Http::sessionName($session_name);
                switch ($type) {
                    case 'file':
                        Session::handlerClass(FileSessionHandler::class, $config);
                        break;
                    case 'redis':
                        Session::handlerClass(RedisSessionHandler::class, $config);
                        break;
                }
            }
            if (in_array('onWorkerStart', $worker->config['callback'] ?? [])) {
                if (!method_exists("\\App\\Callback\\{$worker->name}\\onWorkerStart", "init")) {
                    CreateFile::create("\\App\\Callback\\{$worker->name}\\onWorkerStart", "WorkerMan");
                }
                call_user_func("\\App\\Callback\\{$worker->name}\\onWorkerStart::init", $worker);
            }
        };

        $callback_map = [
            'onWorkerReload',
            'onConnect',
            'onMessage',
            'onClose',
            'onError',
            'onBufferFull',
            'onBufferDrain',
            'onWorkerStop'
        ];
        foreach ($callback_map as $name) {
            if (!in_array($name, $config['callback'] ?? [])) {
                continue;
            }
            if (!method_exists("\\App\\Callback\\{$process_name}\\{$name}", "init")) {
                CreateFile::create("\\App\\Callback\\{$process_name}\\{$name}", "WorkerMan");
            }
            $worker->$name = ["\\App\\Callback\\{$process_name}\\{$name}", "init"];
        }
    }
}

if (!empty($gateway_worker_process)) {
    if (!method_exists("\\App\\Callback\\Events", "onWorkerStart")) {
        CreateFile::Events();
    }

    foreach ($gateway_worker_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);

        $callback_map = [
            'onWorkerStart',
            'onConnect',
            'onWebSocketConnect',
            'onMessage',
            'onClose',
            'onWorkerStop'
        ];
        foreach ($callback_map as $name) {
            if (!in_array($name, $config['callback'] ?? []) || empty($config['businessCount'])) {
                continue;
            }
            if (!method_exists("\\App\\Callback\\{$process_name}\\{$name}", "init")) {
                CreateFile::create("\\App\\Callback\\{$process_name}\\{$name}", "GatewayWorker");
            }
        }

        if (in_array('Register', $config['type'] ?? [])) {
            $register             = new Register("text://" . $config['registerAddress']);
            $register->name       = $process_name;
            $register->secretKey  = $config['secretKey'] ?? '';
            $register->reloadable = $config['reloadable'] ?? false;
        }

        if (in_array('Gateway', $config['type'] ?? [])) {
            $gateway        = new Gateway($config['listen'], $config['context'] ?? []);
            $gateway->name  = $process_name;
            $gateway->count = $config['gatewayCount'];

            $property_map = [
                'transport',
                'lanIp',
                'startPort',
                'pingInterval',
                'pingNotResponseLimit',
                'pingData',
                'registerAddress',
                'secretKey',
                'reloadable',
                'router',
                'sendToWorkerBufferSize',
                'sendToClientBufferSize',
                'protocolAccelerate',
            ];
            foreach ($property_map as $property) {
                if (isset($config[$property])) {
                    $gateway->$property = $config[$property];
                }
            }
        }

        if (in_array('BusinessWorker', $config['type'] ?? [])) {
            $bussiness               = new BusinessWorker();
            $bussiness->name         = $process_name;
            $bussiness->count        = $config['businessCount'];
            $bussiness->eventHandler = '\\App\\Callback\\Events';

            $property_map = [
                'registerAddress',
                'processTimeout',
                'processTimeoutHandler',
                'secretKey',
                'sendToGatewayBufferSize',
            ];
            foreach ($property_map as $property) {
                if (isset($config[$property])) {
                    $bussiness->$property = $config[$property];
                }
            }
        }

        if (!defined($process_name . "Register")) {
            define($process_name . "Register", $config['registerAddress'] ?? '');
        }
    }
}

if (!empty($global_data_process)) {
    foreach ($global_data_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);

        new GlobalData\Server($config['listen_ip'], $config['listen_port']);

        if (!defined("GlobalData" . $process_name)) {
            define("GlobalData" . $process_name, $config['listen_ip'] . ":" . $config['listen_port']);
        }
    }
}

if (!empty($channel_process)) {
    foreach ($channel_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);

        new Channel\Server($config['listen_ip'], $config['listen_port']);

        if (!defined("Channel" . $process_name . "Ip")) {
            define("Channel" . $process_name . "Ip", $config['listen_ip']);
        }
        if (!defined("Channel" . $process_name . "Port")) {
            define("Channel" . $process_name . "Port", $config['listen_port']);
        }
    }
}

if (!empty($async_process['client'])) {
    $worker        = new Worker();
    $worker->count = 1;
    $worker->name  = 'Async';
    $worker->onWorkerStart = function ($worker) use (&$async_process) {
        Log::start($worker);

        $redis = $async_process['config'];
        $queue = new \Workerman\RedisQueue\Client('redis://' . $redis['host'] . ':' . $redis['port'], ['auth' => $redis['password']]);

        foreach ($async_process['client'] as $process_name => $config) {
            $process_name = "Async" . parse_name($process_name, 1);

            list($scheme, $address) = \explode(':', $config['listen'], 2);

            if ($scheme === 'udp') {
                ${$process_name} = new AsyncUdpConnection($config['listen'], $config['context'] ?? []);
            } else {
                ${$process_name} = new AsyncTcpConnection($config['listen'], $config['context'] ?? []);

                if (isset($config['transport'])) {
                    ${$process_name}->transport = $config['transport'];
                }
            }

            $callback_map = [
                'onConnect',
                'onMessage',
                'onClose',
                'onError',
                'onBufferFull',
                'onBufferDrain'
            ];
            foreach ($callback_map as $name) {
                if (!in_array($name, $config['callback'] ?? [])) {
                    continue;
                }
                if (!method_exists("\\App\\Callback\\{$process_name}\\{$name}", "init")) {
                    CreateFile::create("\\App\\Callback\\{$process_name}\\{$name}", "Async");
                }
                ${$process_name}->$name = ["\\App\\Callback\\{$process_name}\\{$name}", "init"];
            }

            ${$process_name}->queue = $queue;
            ${$process_name}->connect();
        }
    };
}

Worker::runAll();
