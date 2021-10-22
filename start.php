<?php

require_once __DIR__ . '/vendor/autoload.php';

if (strpos(strtolower(PHP_OS), 'win') === 0) {
    exit("start.php not support windows\n");
}

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use GatewayWorker\BusinessWorker;
use support\base\Config;
use support\bootstrap\Container;

if (!is_dir(runtime_path())) {
    mkdir(runtime_path(), 0777, true);
}

Config::load(config_path());

$app = (array)config('app', []);

date_default_timezone_set($app['default_timezone'] ?? 'Asia/Shanghai');

Worker::$logFile                      = $app['log_file'] ?? runtime_path() . '/master.log';
Worker::$pidFile                      = $app['pid_file'] ?? runtime_path() . '/master.pid';
Worker::$stdoutFile                   = $app['stdout_file'] ?? runtime_path() . '/stdout.log';
TcpConnection::$defaultMaxPackageSize = $app['defaultMaxPackageSize'] ?? 10 * 1024 * 1024;

foreach (config('workerman', []) as $processName => $config) {
    $worker       = new Worker($config['listen'] ?? null, $config['context'] ?? []);
    $worker->name = $processName;

    $propertyMap = [
        'count',
        'user',
        'group',
        'reloadable',
        'reusePort',
        'transport',
    ];
    foreach ($propertyMap as $property) {
        if (isset($config[$property])) {
            $worker->$property = $config[$property];
        }
    }

    $worker->onWorkerStart = function ($worker) use ($config) {
        Config::reload(config_path());

        foreach (config('autoload.files', []) as $file) {
            include_once $file;
        }

        $bootstrap = $config['bootstrap'] ?? config('bootstrap', []);
        if (!in_array(\support\bootstrap\Log::class, $bootstrap)) {
            $bootstrap[] = \support\bootstrap\Log::class;
        }
        foreach (config('bootstrap', []) as $className) {
            $className::start($worker);
        }

        if (isset($config['handler']) && !class_exists($config['handler'])) {
            echo "process error: class {$config['handler']} not exists\r\n";
        }

        $class = Container::make("\\support\\callback\\Workerman");
        worker_bind($worker, $class);
    };
}

foreach (config('gateway_worker', []) as $processName => $config) {
    if (!empty($config['type']) && 'Register' === $config['type']) {
        $register             = new Register("text://" . $config['registerAddress']);
        $register->name       = $processName;
        $register->secretKey  = $config['secretKey'] ?? '';
        $register->reloadable = $config['reloadable'] ?? false;
    }

    if (!empty($config['type']) && 'Gateway' === $config['type']) {
        $gateway        = new Gateway($config['listen'], $config['context'] ?? []);
        $gateway->name  = $processName;
        $gateway->count = $config['count'];

        $propertyMap = [
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
        foreach ($propertyMap as $property) {
            if (isset($config[$property])) {
                $gateway->$property = $config[$property];
            }
        }
    }

    if (!empty($config['type']) && 'BusinessWorker' === $config['type']) {
        if (isset($config['handler']) && !class_exists($config['handler'])) {
            echo "process error: class {$config['handler']} not exists\r\n";
            continue;
        }

        $bussiness               = new BusinessWorker();
        $bussiness->name         = $processName;
        $bussiness->count        = $config['count'];
        $bussiness->eventHandler = "\\support\\callback\\GatewayWorker";

        $propertyMap = [
            'registerAddress',
            'processTimeout',
            'processTimeoutHandler',
            'secretKey',
            'sendToGatewayBufferSize',
        ];
        foreach ($propertyMap as $property) {
            if (isset($config[$property])) {
                $bussiness->$property = $config[$property];
            }
        }
    }
}

Worker::runAll();
