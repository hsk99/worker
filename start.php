<?php 

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/support/helpers.php';

use \Workerman\Worker;
use \GatewayWorker\Gateway;
use \GatewayWorker\Register;
use \GatewayWorker\BusinessWorker;
use \support\bootstrap\Config;
use \support\bootstrap\CreateFile;

load_files(bootstrap_path());
load_files(extend_path());
Config::load(config_path());

if (!is_dir(runtime_path())) {
    mkdir(runtime_path(), 0777, true);
}
\Workerman\Worker::$logFile    = runtime_path(). '/workerman.log';
\Workerman\Worker::$pidFile    = runtime_path(). '/workerman.pid';
\Workerman\Worker::$stdoutFile = runtime_path(). '/stdout.log';

$process = config('process', []);

$worker_names = array_merge(array_keys($process['workerman']), array_keys($process['gateway_worker']));
if (count($worker_names) != count(array_unique($worker_names))) {  
   throw new Exception("There are duplicates in the process names of WorkerMan and GatewayWorker");
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
        $callback_map = [
            'onWorkerStart',
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
            if (!in_array($name, $config['callback'])) {
                continue;
            }
            if (!method_exists("\\App\\Callback\\{$process_name}\\{$name}", "init")) {
                CreateFile::create("\\App\\Callback\\{$process_name}\\{$name}", "GatewayWorker");
            }
        }

        $register                   = new Register("text://" . $config['register']);
        $register->name             = $process_name;
        $gateway                    = new Gateway($config['listen'], $config['context'] ?? []);
        $gateway->transport         = $config['transport'] ?? 'tcp';
        $gateway->name              = $process_name;
        $gateway->count             = $config['count'];
        $gateway->lanIp             = $config['lan_ip'];
        $gateway->startPort         = $config['start_port'];
        $gateway->pingInterval      = $config['pinginterval'];
        $gateway->pingData          = $config['pingdata'];
        $gateway->registerAddress   = $config['register'];
        $bussiness                  = new BusinessWorker();
        $bussiness->name            = $process_name;
        $bussiness->count           = $config['business_count'];
        $bussiness->registerAddress = $config['register'];
        $bussiness->eventHandler    = '\\App\\Callback\\Events';
    }
}

Worker::runAll();