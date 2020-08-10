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

$process = config('process', []);

$worker_names = array_merge(array_keys($process['workerman']), array_keys($process['gateway_worker']));
if (count($worker_names) != count(array_unique($worker_names))) {  
   exit("There are duplicates in the process names of WorkerMan and GatewayWorker");
}


$str = "<?php \n\r";
$str .= "\n\r";
$str .= "ini_set('display_errors', 'on');\n\r";
$str .= "\n\r";
$str .= "date_default_timezone_set('Asia/Shanghai');\n\r";
$str .= "\n\r";
$str .= "require_once __DIR__ . '/../vendor/autoload.php';\n\r";
$str .= "require_once __DIR__ . '/../support/helpers.php';\n\r";
$str .= "\n\r";
$str .= "use Workerman\Worker;\n\r";
$str .= "use support\bootstrap\Config;\n\r";
$str .= "\n\r";
$str .= "load_files(app_path());\n\r";
$str .= "load_files(bootstrap_path());\n\r";
$str .= "load_files(extend_path());\n\r";
$str .= "Config::load(config_path());\n\r";
$str .= "\n\r";
$str .= "if (!is_dir(runtime_path())) {\n\r";
$str .= "    mkdir(runtime_path(), 0777, true);\n\r";
$str .= "}\n\r";
$str .= "Worker::\$logFile                      = runtime_path(). '/workerman.log';\n\r";
$str .= "Worker::\$pidFile                      = runtime_path(). '/workerman.pid';\n\r";
$str .= "Worker::\$stdoutFile                   = runtime_path(). '/stdout.log';\n\r";
$str .= "\n\r";
if (!empty($process['gateway_worker'])) {
	foreach ($process['gateway_worker'] as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $str .= "if (!defined('{$process_name}Register')) {\n\r";
		$str .= "    define('{$process_name}Register', '{$config['register']}');\n\r";
		$str .= "}\n\r";
		$str .= "\n\r";
    }
}
if (!empty($process['global_data'])) {
	foreach ($process['global_data'] as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $str .= "if (!defined('GlobalData{$process_name}')) {\n\r";
		$str .= "    define('GlobalData{$process_name}', '{$config['listen_ip']}:{$config['listen_port']}');\n\r";
		$str .= "}\n\r";
		$str .= "\n\r";
    }
}
if (!empty($process['channel'])) {
	foreach ($process['channel'] as $process_name => $config) {
        $process_name = parse_name($process_name, 1);
        $str .= "if (!defined('Channel{$process_name}Ip')) {\n\r";
		$str .= "    define('Channel{$process_name}Ip', '{$config['listen_ip']}');\n\r";
		$str .= "}\n\r";
		$str .= "\n\r";
		$str .= "if (!defined('Channel{$process_name}Port')) {\n\r";
		$str .= "    define('Channel{$process_name}Port', '{$config['listen_port']}');\n\r";
		$str .= "}\n\r";
		$str .= "\n\r";
    }
}

$ok = file_put_contents(base_path() . DS . 'win' . DS . "loader.php", $str);
if (!$ok) {
    exit("Failed to create file");
}

$bat = "CHCP 65001\n\rphp";

if (!empty($process['workerman'])) {
    foreach ($process['workerman'] as $process_name => $config) {
		$process_name = parse_name($process_name, 1);
		$listen       = $config['listen'] ?? null;

        $str = "<?php \n\r";
        $str .= "\n\r";
		$str .= "require_once __DIR__ . '/loader.php';\n\r";
		$str .= "\n\r";
		$str .= "use Workerman\Worker;\n\r";
		$str .= "use Workerman\Connection\TcpConnection;\n\r";
		$str .= "\n\r";
		$str .= "TcpConnection::\$defaultMaxPackageSize = 10*1024*1024;\n\r";
        $str .= "\n\r";
        $str .= "\$worker                 = new Worker(\"" . $listen . "\");\n\r";
		$str .= "\$worker->name           = '$process_name';\n\r";

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
                $str .= $parameter . $config[$property] . ";\n\r";
            }
        }
        
        $callback_map = [
			'onWorkerStart'  => "\$worker->onWorkerStart  = ",
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
            if (!in_array($name, $config['callback'])) {
                continue;
            }
            if (!method_exists("\\App\\Callback\\{$process_name}\\{$name}", "init")) {
                CreateFile::create("\\App\\Callback\\{$process_name}\\{$name}", "WorkerMan");
            }
            $str .= $parameter . '["\\\\App\\\\Callback\\\\' . $process_name . '\\\\' . $name . '", "init"]' . ";\n\r";
        }

        $str .= "\n\r";
		$str .= "Worker::runAll();\n\r";
        
        
        $ok = file_put_contents(base_path() . DS . 'win' . DS . $process_name . ".php", $str);
        if (!$ok) {
            exit("Failed to create file");
        }
        $bat .= " " . 'win' . DS . $process_name . ".php";
    }
}

if (!empty($process['gateway_worker'])) {
    if (!method_exists("\\App\\Callback\\Events", "onWorkerStart")) {
        CreateFile::Events();
    }

    foreach ($process['gateway_worker'] as $process_name => $config) {
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
            if (!in_array($name, $config['callback'])) {
                continue;
            }
            if (!method_exists("\\App\\Callback\\{$process_name}\\{$name}", "init")) {
                CreateFile::create("\\App\\Callback\\{$process_name}\\{$name}", "GatewayWorker");
            }
        }

        $register = "<?php \n\r";
        $register .= "\n\r";
		$register .= "require_once __DIR__ . '/loader.php';\n\r";
		$register .= "\n\r";
		$register .= "use Workerman\Worker;\n\r";
		$register .= "use GatewayWorker\Register;\n\r";
        $register .= "\n\r";
        $register .= "\$register       = new Register(\"text://" . $config['register'] . "\");\n\r";
		$register .= "\$register->name = '$process_name';\n\r";
		$register .= "\n\r";
		$register .= "Worker::runAll();\n\r";

		$ok = file_put_contents(base_path() . DS . 'win' . DS . $process_name . "Register.php", $register);
        if (!$ok) {
            exit("Failed to create file");
        }
        $bat .= " " . 'win' . DS . $process_name . "Register.php";

		$gateway = "<?php \n\r";
		$gateway .= "\n\r";
		$gateway .= "require_once __DIR__ . '/loader.php';\n\r";
		$gateway .= "\n\r";
		$gateway .= "use Workerman\Worker;\n\r";
		$gateway .= "use GatewayWorker\Gateway;\n\r";
        $gateway .= "\n\r";
		$gateway .= "\$gateway                  = new Gateway(\"" . $listen . "\");\n\r";
		$gateway .= "\$gateway->transport       = '$transport';\n\r";
		$gateway .= "\$gateway->name            = '$process_name';\n\r";
		$gateway .= "\$gateway->count           = " . $config['count'] . ";\n\r";
		$gateway .= "\$gateway->lanIp           = '" . $config['lan_ip'] . "';\n\r";
		$gateway .= "\$gateway->startPort       = '" . $config['start_port'] . "';\n\r";
		$gateway .= "\$gateway->pingInterval    = '" . $config['pinginterval'] . "';\n\r";
		$gateway .= "\$gateway->pingData        = '" . $config['pingdata'] . "';\n\r";
		$gateway .= "\$gateway->registerAddress = '" . $config['register'] . "';\n\r";
		$gateway .= "\n\r";
		$gateway .= "Worker::runAll();\n\r";

		$ok = file_put_contents(base_path() . DS . 'win' . DS . $process_name . "Gateway.php", $gateway);
        if (!$ok) {
            exit("Failed to create file");
        }
        $bat .= " " . 'win' . DS . $process_name . "Gateway.php";

		$bussiness = "<?php \n\r";
		$bussiness .= "\n\r";
		$bussiness .= "require_once __DIR__ . '/loader.php';\n\r";
		$bussiness .= "\n\r";
		$bussiness .= "use Workerman\Worker;\n\r";
		$bussiness .= "use GatewayWorker\BusinessWorker;\n\r";
        $bussiness .= "\n\r";
		$bussiness .= "\$bussiness                  = new BusinessWorker();\n\r";
		$bussiness .= "\$bussiness->name            = '$process_name';\n\r";
		$bussiness .= "\$bussiness->count           = " . $config['business_count'] . ";\n\r";
		$bussiness .= "\$bussiness->registerAddress = '" . $config['register'] . "';\n\r";
		$bussiness .= "\$bussiness->eventHandler    = '\\\\App\\\\Callback\\\\Events';\n\r";
		$bussiness .= "\n\r";
		$bussiness .= "Worker::runAll();\n\r";

		$ok = file_put_contents(base_path() . DS . 'win' . DS . $process_name . "Bussiness.php", $bussiness);
        if (!$ok) {
            exit("Failed to create file");
        }
        $bat .= " " . 'win' . DS . $process_name . "Bussiness.php";
    }
}

if (!empty($process['global_data'])) {
    foreach ($process['global_data'] as $process_name => $config) {
		$process_name = parse_name($process_name, 1);

        $str = "<?php \n\r";
        $str .= "\n\r";
		$str .= "require_once __DIR__ . '/loader.php';\n\r";
		$str .= "\n\r";
		$str .= "use Workerman\Worker;\n\r";
        $str .= "\n\r";
        $str .= "new GlobalData\Server('" . $config['listen_ip'] . "', {$config['listen_port']});\n\r";
        $str .= "\n\r";
		$str .= "Worker::runAll();\n\r";
        
        
        $ok = file_put_contents(base_path() . DS . 'win' . DS . "GlobalData" . $process_name . ".php", $str);
        if (!$ok) {
            exit("Failed to create file");
        }
        $bat .= " " . 'win' . DS . "GlobalData" . $process_name . ".php";
    }
}

if (!empty($process['channel'])) {
    foreach ($process['channel'] as $process_name => $config) {
		$process_name = parse_name($process_name, 1);

        $str = "<?php \n\r";
        $str .= "\n\r";
		$str .= "require_once __DIR__ . '/loader.php';\n\r";
		$str .= "\n\r";
		$str .= "use Workerman\Worker;\n\r";
        $str .= "\n\r";
        $str .= "new Channel\Server('" . $config['listen_ip'] . "', {$config['listen_port']});\n\r";
        $str .= "\n\r";
		$str .= "Worker::runAll();\n\r";
        
        
        $ok = file_put_contents(base_path() . DS . 'win' . DS . "Channel" . $process_name . ".php", $str);
        if (!$ok) {
            exit("Failed to create file");
        }
        $bat .= " " . 'win' . DS . "Channel" . $process_name . ".php";
    }
}

$ok = file_put_contents(base_path() . DS . "start.bat", $bat);
if (!$ok) {
    exit("Failed to create file");
}