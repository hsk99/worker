<?php

require_once __DIR__ . '/support/helpers.php';

use support\bootstrap\Config;
use support\bootstrap\CreateFile;

if (is_dir(app_path())) {
    load_files(app_path());
}
load_files(bootstrap_path());
load_files(extend_path());
Config::load(config_path());

delete_dir_file(base_path() . DS . 'win');
@unlink(base_path() . DS . "start.bat");
if (!is_dir(base_path() . DS . 'win')) {
    mkdir(base_path() . DS . 'win', 0777, true);
}

$workerman_process      = config('workerman', []);
$gateway_worker_process = config('gateway_worker', []);
$global_data_process    = config('global_data', []);
$channel_process        = config('channel', []);
$async_process          = config('async', []);

if (!empty($workerman_process) && !empty($gateway_worker_process)) {
    $worker_names = array_merge(array_keys($workerman_process), array_keys($gateway_worker_process));
    if (count($worker_names) != count(array_unique($worker_names))) {
        throw new Exception("There are duplicates in the process names of WorkerMan and GatewayWorker");
    }
}


$str = "<?php \n";
$str .= "\n";
$str .= "ini_set('display_errors', 'on');\n";
$str .= "\n";
$str .= "date_default_timezone_set('Asia/Shanghai');\n";
$str .= "\n";
$str .= "require_once __DIR__ . '/../vendor/autoload.php';\n";
$str .= "require_once __DIR__ . '/../support/helpers.php';\n";
$str .= "\n";
$str .= "use Workerman\Worker;\n";
$str .= "use support\bootstrap\Config;\n";
$str .= "\n";
$str .= "load_files(protocols_path());\n";
$str .= "load_files(app_path());\n";
$str .= "load_files(bootstrap_path());\n";
$str .= "load_files(extend_path());\n";
$str .= "Config::load(config_path());\n";
$str .= "\n";
$str .= "if (!is_dir(runtime_path())) {\n";
$str .= "    mkdir(runtime_path(), 0777, true);\n";
$str .= "}\n";
$str .= "Worker::\$logFile                      = runtime_path(). '/workerman.log';\n";
$str .= "Worker::\$pidFile                      = runtime_path(). '/workerman.pid';\n";
$str .= "Worker::\$stdoutFile                   = runtime_path(). '/stdout.log';\n";
$str .= "\n";
if (!empty($gateway_worker_process)) {
    foreach ($gateway_worker_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $register     = $config['register'] ?? '';

        $str .= "if (!defined('{$process_name}Register')) {\n";
        $str .= "    define('{$process_name}Register', '{$register}');\n";
        $str .= "}\n";
        $str .= "\n";
    }
}
if (!empty($global_data_process)) {
    foreach ($global_data_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $str .= "if (!defined('GlobalData{$process_name}')) {\n";
        $str .= "    define('GlobalData{$process_name}', '{$config['listen_ip']}:{$config['listen_port']}');\n";
        $str .= "}\n";
        $str .= "\n";
    }
}
if (!empty($channel_process)) {
    foreach ($channel_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $str .= "if (!defined('Channel{$process_name}Ip')) {\n";
        $str .= "    define('Channel{$process_name}Ip', '{$config['listen_ip']}');\n";
        $str .= "}\n";
        $str .= "\n";
        $str .= "if (!defined('Channel{$process_name}Port')) {\n";
        $str .= "    define('Channel{$process_name}Port', '{$config['listen_port']}');\n";
        $str .= "}\n";
        $str .= "\n";
    }
}

$ok = file_put_contents(base_path() . DS . 'win' . DS . "loader.php", $str);
if (!$ok) {
    exit("Failed to create file");
}

$bat = "CHCP 65001\nphp";

if (!empty($workerman_process)) {
    foreach ($workerman_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $listen       = $config['listen'] ?? null;

        $str = "<?php \n";
        $str .= "\n";
        $str .= "require_once __DIR__ . '/loader.php';\n";
        $str .= "\n";
        $str .= "use support\bootstrap\CreateFile;\n";
        $str .= "use Workerman\Worker;\n";
        $str .= "use Workerman\Connection\TcpConnection;\n";
        $str .= "use Workerman\Protocols\Http;\n";
        $str .= "use Workerman\Protocols\Http\Session;\n";
        $str .= "use Workerman\Protocols\Http\Session\FileSessionHandler;\n";
        $str .= "use Workerman\Protocols\Http\Session\RedisSessionHandler;\n";
        $str .= "\n";
        $str .= "TcpConnection::\$defaultMaxPackageSize = 10*1024*1024;\n";
        $str .= "\n";
        $str .= "\$worker                 = new Worker(\"" . $listen . "\");\n";
        $str .= "\$worker->name           = '$process_name';\n";

        $property_map = [
            'count'      => "\$worker->count          = ",
            'user'       => "\$worker->user           = ",
            'group'      => "\$worker->group          = ",
            'reloadable' => "\$worker->reloadable     = ",
            'reusePort'  => "\$worker->reusePort      = ",
            'transport'  => "\$worker->transport      = ",
        ];
        foreach ($property_map as $property => $parameter) {
            if (isset($config[$property])) {
                $str .= $parameter . $config[$property] . ";\n";
            }
        }

        $str .= "\$worker->config         = '" . serialize($config) . "';\n";
        $str .= "\$worker->onWorkerStart = function (\$worker) {\n";
        $str .= "    foreach (config('autoload.files', []) as \$file) {\n";
        $str .= "        include_once \$file;\n";
        $str .= "    }\n";
        $str .= "    \$worker->config = unserialize(\$worker->config);\n";
        $str .= "    if (in_array(\$worker->protocol, [\"\\Workerman\\Protocols\\Http\", \"Workerman\\Protocols\\Http\"])) {\n";
        $str .= "        \$session      = \$worker->config['session'] ?? [];\n";
        $str .= "        \$type         = \$session['type'] ?? 'file';\n";
        $str .= "        \$session_name = \$session['session_name'] ?? 'PHPSID';\n";
        $str .= "        \$config       = \$session['config'][\$type] ?? ['save_path' => runtime_path() . DS . 'sessions'];\n";
        $str .= "\n";
        $str .= "        Http::sessionName(\$session_name);\n";
        $str .= "        switch (\$type) {\n";
        $str .= "            case 'file':\n";
        $str .= "                Session::handlerClass(FileSessionHandler::class, \$config);\n";
        $str .= "                break;\n";
        $str .= "            case 'redis':\n";
        $str .= "                Session::handlerClass(RedisSessionHandler::class, \$config);\n";
        $str .= "                break;\n";
        $str .= "        }\n";
        $str .= "    }\n";
        $str .= "    if (in_array('onWorkerStart', \$worker->config['callback'])) {\n";
        $str .= "        if (!method_exists(\"\\\\App\\\\Callback\\\\{\$worker->name}\\\\onWorkerStart\", \"init\")) {\n";
        $str .= "            CreateFile::create(\"\\\\App\\\\Callback\\\\{\$worker->name}\\\\onWorkerStart\", \"WorkerMan\");\n";
        $str .= "        }\n";
        $str .= "        call_user_func(\"\\\\App\\\\Callback\\\\{\$worker->name}\\\\onWorkerStart::init\", \$worker);\n";
        $str .= "    }\n";
        $str .= "};\n";

        $callback_map = [
            'onWorkerReload' => "\$worker->onWorkerReload = ",
            'onConnect'      => "\$worker->onConnect      = ",
            'onMessage'      => "\$worker->onMessage      = ",
            'onClose'        => "\$worker->onClose        = ",
            'onError'        => "\$worker->onError        = ",
            'onBufferFull'   => "\$worker->onBufferFull   = ",
            'onBufferDrain'  => "\$worker->onBufferDrain  = ",
            'onWorkerStop'   => "\$worker->onWorkerStop   = ",
        ];
        foreach ($callback_map as $name => $parameter) {
            if (!in_array($name, $config['callback'] ?? [])) {
                continue;
            }
            if (!method_exists("\\App\\Callback\\{$process_name}\\{$name}", "init")) {
                CreateFile::create("\\App\\Callback\\{$process_name}\\{$name}", "WorkerMan");
            }
            $str .= $parameter . '["\\\\App\\\\Callback\\\\' . $process_name . '\\\\' . $name . '", "init"]' . ";\n";
        }

        $str .= "\n";
        $str .= "Worker::runAll();\n";


        $ok = file_put_contents(base_path() . DS . 'win' . DS . $process_name . ".php", $str);
        if (!$ok) {
            exit("Failed to create file");
        }
        $bat .= " " . 'win' . DS . $process_name . ".php";
    }
}

if (!empty($gateway_worker_process)) {
    if (!method_exists("\\App\\Callback\\Events", "onWorkerStart")) {
        CreateFile::Events();
    }

    foreach ($gateway_worker_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $listen       = $config['listen'] ?? null;
        $transport    = $config['transport'] ?? 'tcp';

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
            $register = "<?php \n";
            $register .= "\n";
            $register .= "require_once __DIR__ . '/loader.php';\n";
            $register .= "\n";
            $register .= "use Workerman\Worker;\n";
            $register .= "use GatewayWorker\Register;\n";
            $register .= "\n";
            $register .= "\$register       = new Register(\"text://" . $config['register'] . "\");\n";
            $register .= "\$register->name = '$process_name';\n";
            $register .= "\n";
            $register .= "Worker::runAll();\n";

            $ok = file_put_contents(base_path() . DS . 'win' . DS . $process_name . "Register.php", $register);
            if (!$ok) {
                exit("Failed to create file");
            }
            $bat .= " " . 'win' . DS . $process_name . "Register.php";
        }

        if (in_array('Gateway', $config['type'] ?? [])) {
            $gateway = "<?php \n";
            $gateway .= "\n";
            $gateway .= "require_once __DIR__ . '/loader.php';\n";
            $gateway .= "\n";
            $gateway .= "use Workerman\Worker;\n";
            $gateway .= "use GatewayWorker\Gateway;\n";
            $gateway .= "\n";
            $gateway .= "\$gateway                  = new Gateway(\"" . $listen . "\");\n";
            $gateway .= "\$gateway->transport       = '$transport';\n";
            $gateway .= "\$gateway->name            = '$process_name';\n";
            $gateway .= "\$gateway->count           = " . $config['count'] . ";\n";
            $gateway .= "\$gateway->lanIp           = '" . $config['lan_ip'] . "';\n";
            $gateway .= "\$gateway->startPort       = '" . $config['start_port'] . "';\n";
            $gateway .= "\$gateway->pingInterval    = '" . $config['pinginterval'] . "';\n";
            $gateway .= "\$gateway->pingData        = '" . $config['pingdata'] . "';\n";
            $gateway .= "\$gateway->registerAddress = '" . $config['register'] . "';\n";
            $gateway .= "\n";
            $gateway .= "Worker::runAll();\n";

            $ok = file_put_contents(base_path() . DS . 'win' . DS . $process_name . "Gateway.php", $gateway);
            if (!$ok) {
                exit("Failed to create file");
            }
            $bat .= " " . 'win' . DS . $process_name . "Gateway.php";
        }

        if (in_array('BusinessWorker', $config['type'] ?? [])) {
            $bussiness = "<?php \n";
            $bussiness .= "\n";
            $bussiness .= "require_once __DIR__ . '/loader.php';\n";
            $bussiness .= "\n";
            $bussiness .= "use Workerman\Worker;\n";
            $bussiness .= "use GatewayWorker\BusinessWorker;\n";
            $bussiness .= "\n";
            $bussiness .= "\$bussiness                  = new BusinessWorker();\n";
            $bussiness .= "\$bussiness->name            = '$process_name';\n";
            $bussiness .= "\$bussiness->count           = " . $config['business_count'] . ";\n";
            $bussiness .= "\$bussiness->registerAddress = '" . $config['register'] . "';\n";
            $bussiness .= "\$bussiness->eventHandler    = '\\\\App\\\\Callback\\\\Events';\n";
            $bussiness .= "\n";
            $bussiness .= "Worker::runAll();\n";

            $ok = file_put_contents(base_path() . DS . 'win' . DS . $process_name . "Bussiness.php", $bussiness);
            if (!$ok) {
                exit("Failed to create file");
            }
            $bat .= " " . 'win' . DS . $process_name . "Bussiness.php";
        }
    }
}

if (!empty($global_data_process)) {
    foreach ($global_data_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);

        $str = "<?php \n";
        $str .= "\n";
        $str .= "require_once __DIR__ . '/loader.php';\n";
        $str .= "\n";
        $str .= "use Workerman\Worker;\n";
        $str .= "\n";
        $str .= "new GlobalData\Server('" . $config['listen_ip'] . "', {$config['listen_port']});\n";
        $str .= "\n";
        $str .= "Worker::runAll();\n";


        $ok = file_put_contents(base_path() . DS . 'win' . DS . "GlobalData" . $process_name . ".php", $str);
        if (!$ok) {
            exit("Failed to create file");
        }
        $bat .= " " . 'win' . DS . "GlobalData" . $process_name . ".php";
    }
}

if (!empty($channel_process)) {
    foreach ($channel_process as $process_name => $config) {
        $process_name = parse_name($process_name, 1);

        $str = "<?php \n";
        $str .= "\n";
        $str .= "require_once __DIR__ . '/loader.php';\n";
        $str .= "\n";
        $str .= "use Workerman\Worker;\n";
        $str .= "\n";
        $str .= "new Channel\Server('" . $config['listen_ip'] . "', {$config['listen_port']});\n";
        $str .= "\n";
        $str .= "Worker::runAll();\n";


        $ok = file_put_contents(base_path() . DS . 'win' . DS . "Channel" . $process_name . ".php", $str);
        if (!$ok) {
            exit("Failed to create file");
        }
        $bat .= " " . 'win' . DS . "Channel" . $process_name . ".php";
    }
}

if (!empty($async_process['client'])) {
    $redis = $async_process['config'];

    $str = "<?php \n";
    $str .= "\n";
    $str .= "require_once __DIR__ . '/loader.php';\n";
    $str .= "\n";
    $str .= "use support\bootstrap\CreateFile;\n";
    $str .= "use Workerman\Worker;\n";
    $str .= "use Workerman\Lib\Timer;\n";
    $str .= "use Workerman\Connection\AsyncTcpConnection;\n";
    $str .= "\n";
    $str .= "\$worker        = new Worker();\n";
    $str .= "\$worker->count = 1;\n";
    $str .= "\$worker->name  = 'Async';\n";
    $str .= "\$worker->onWorkerStart = function (\$worker) {\n";
    $str .= "    \$queue = new \Workerman\RedisQueue\Client('redis://{$redis['host']}:{$redis['port']}', ['auth' => '{$redis['password']}']);\n";
    $str .= "    \n";

    foreach ($async_process['client'] as $process_name => $config) {
        $process_name = parse_name($process_name, 1);

        $str .= "    \$worker->{$process_name}_config = '" . serialize($config) . "';\n";
        $str .= "    Timer::add(1, function () use (&\$worker, &\$queue) {\n";
        $str .= "        \$config             = unserialize(\$worker->{$process_name}_config );\n";
        $str .= "        \${$process_name}                = new AsyncTcpConnection(\$config['listen'], \$config['context'] ?? []);\n";
        $str .= "        \${$process_name}->transport     = \$config['transport'] ?? 'tcp';\n";

        $callback_map = [
            'onConnect'      => "\${$process_name}->onConnect     = ",
            'onMessage'      => "\${$process_name}->onMessage     = ",
            'onClose'        => "\${$process_name}->onClose       = ",
            'onError'        => "\${$process_name}->onError       = ",
            'onBufferFull'   => "\${$process_name}->onBufferFull  = ",
            'onBufferDrain'  => "\${$process_name}->onBufferDrain = ",
        ];
        foreach ($callback_map as $name => $parameter) {
            if (!in_array($name, $config['callback'] ?? [])) {
                continue;
            }
            if (!method_exists("\\App\\Callback\\Async{$process_name}\\{$name}", "init")) {
                CreateFile::create("\\App\\Callback\\Async{$process_name}\\{$name}", "Async");
            }
            $str .= "        " . $parameter . '["\\\\App\\\\Callback\\\\Async' . $process_name . '\\\\' . $name . '", "init"]' . ";\n";
        }

        $str .= "        \${$process_name}->queue         = \$queue;\n";
        $str .= "        \${$process_name}->connect();\n";
        $str .= "    }, '', false);\n";
        $str .= "    \n";
    }



    $str .= "};\n";
    $str .= "\n";

    $str .= "Worker::runAll();\n";


    $ok = file_put_contents(base_path() . DS . 'win' . DS . "Async.php", $str);
    if (!$ok) {
        exit("Failed to create file");
    }
    $bat .= " " . 'win' . DS . "Async.php";
}

$ok = file_put_contents(base_path() . DS . "start.bat", $bat);
if (!$ok) {
    exit("Failed to create file");
}
