<?php

ini_set('display_errors', 'on');

if (strpos(strtolower(PHP_OS), 'win') === 0) {
    exit("start.php not support windows\n");
}

date_default_timezone_set('Asia/Shanghai');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/support/helpers.php';

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use GatewayWorker\BusinessWorker;
use Workerman\Protocols\Http;
use Workerman\Protocols\Http\Session;
use Workerman\Protocols\Http\Session\FileSessionHandler;
use Workerman\Protocols\Http\Session\RedisSessionHandler;
use support\bootstrap\Config;
use support\bootstrap\CreateFile;

load_files(protocols_path());
load_files(callback_path());
load_files(bootstrap_path());
load_files(extend_path());
Config::load(config_path());

if (!is_dir(runtime_path())) {
    mkdir(runtime_path(), 0777, true);
}
Worker::$logFile                      = runtime_path() . '/workerman.log';
Worker::$pidFile                      = runtime_path() . '/workerman.pid';
Worker::$stdoutFile                   = runtime_path() . '/stdout.log';
TcpConnection::$defaultMaxPackageSize = 10 * 1024 * 1024;

Worker::$onMasterReload = function () {
    Config::reload(config_path());
};

$process = config('process', []);

if (!empty($process['workerman']) && !empty($process['gateway_worker'])) {
    $worker_names = array_merge(array_keys($process['workerman']), array_keys($process['gateway_worker']));
    if (count($worker_names) != count(array_unique($worker_names))) {
        throw new Exception("There are duplicates in the process names of WorkerMan and GatewayWorker");
    }
}

if (!empty($process['workerman'])) {
    foreach ($process['workerman'] as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $worker       = new Worker($config['listen'] ?? null, $config['context'] ?? []);
        $property_map = [
            'count',
            'user',
            'group',
            'reloadable',
            'reusePort',
            'transport',
        ];
        $worker->name = $process_name;
        foreach ($property_map as $property) {
            if (isset($config[$property])) {
                $worker->$property = $config[$property];
            }
        }
        $worker->config = $config;
        $worker->onWorkerStart = function ($worker) {
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
            if (in_array('onWorkerStart', $worker->config['callback'])) {
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
            if (!in_array($name, $config['callback'])) {
                continue;
            }
            if (!method_exists("\\App\\Callback\\{$process_name}\\{$name}", "init")) {
                CreateFile::create("\\App\\Callback\\{$process_name}\\{$name}", "WorkerMan");
            }
            $worker->$name = ["\\App\\Callback\\{$process_name}\\{$name}", "init"];
        }
    }
}

if (!empty($process['gateway_worker'])) {
    if (!method_exists("\\App\\Callback\\Events", "onWorkerStart")) {
        CreateFile::Events();
    }

    foreach ($process['gateway_worker'] as $process_name => $config) {
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
            if (!in_array($name, $config['callback'] ?? []) || empty($config['business_count'])) {
                continue;
            }
            if (!method_exists("\\App\\Callback\\{$process_name}\\{$name}", "init")) {
                CreateFile::create("\\App\\Callback\\{$process_name}\\{$name}", "GatewayWorker");
            }
        }

        if (in_array('Register', $config['type'] ?? [])) {
            $register                   = new Register("text://" . $config['register']);
            $register->name             = $process_name;
        }

        if (in_array('Gateway', $config['type'] ?? [])) {
            $gateway                    = new Gateway($config['listen'], $config['context'] ?? []);
            $gateway->transport         = $config['transport'] ?? 'tcp';
            $gateway->name              = $process_name;
            $gateway->count             = $config['count'];
            $gateway->lanIp             = $config['lan_ip'];
            $gateway->startPort         = $config['start_port'];
            $gateway->pingInterval      = $config['pinginterval'];
            $gateway->pingData          = $config['pingdata'];
            $gateway->registerAddress   = $config['register'];
        }

        if (in_array('BusinessWorker', $config['type'] ?? [])) {
            $bussiness                  = new BusinessWorker();
            $bussiness->name            = $process_name;
            $bussiness->count           = $config['business_count'];
            $bussiness->registerAddress = $config['register'];
            $bussiness->eventHandler    = '\\App\\Callback\\Events';
        }

        if (!defined($process_name . "Register")) {
            define($process_name . "Register", $config['register'] ?? '');
        }
    }
}

if (!empty($process['global_data'])) {
    foreach ($process['global_data'] as $process_name => $config) {
        $process_name = parse_name($process_name, 1);

        new GlobalData\Server($config['listen_ip'], $config['listen_port']);

        if (!defined("GlobalData" . $process_name)) {
            define("GlobalData" . $process_name, $config['listen_ip'] . ":" . $config['listen_port']);
        }
    }
}

if (!empty($process['channel'])) {
    foreach ($process['channel'] as $process_name => $config) {
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

Worker::runAll();
