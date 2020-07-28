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

$bat = "CHCP 65001\n\rphp";

if (!empty($process['workerman'])) {
    foreach ($process['workerman'] as $process_name => $config) {
		$process_name = parse_name($process_name, 1);
		$listen       = $config['listen'] ?? null;

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
		$str .= "use Workerman\Connection\TcpConnection;\n\r";
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
        $register .= "ini_set('display_errors', 'on');\n\r";
		$register .= "\n\r";
		$register .= "date_default_timezone_set('Asia/Shanghai');\n\r";
		$register .= "\n\r";
		$register .= "require_once __DIR__ . '/../vendor/autoload.php';\n\r";
		$register .= "require_once __DIR__ . '/../support/helpers.php';\n\r";
		$register .= "\n\r";
		$register .= "use Workerman\Worker;\n\r";
		$register .= "use GatewayWorker\Register;\n\r";
		$register .= "use support\bootstrap\Config;\n\r";
		$register .= "\n\r";
		$register .= "load_files(app_path());\n\r";
		$register .= "load_files(bootstrap_path());\n\r";
		$register .= "load_files(extend_path());\n\r";
		$register .= "Config::load(config_path());\n\r";
		$register .= "\n\r";
		$register .= "if (!is_dir(runtime_path())) {\n\r";
		$register .= "    mkdir(runtime_path(), 0777, true);\n\r";
		$register .= "}\n\r";
		$register .= "Worker::\$logFile    = runtime_path(). '/workerman.log';\n\r";
		$register .= "Worker::\$pidFile    = runtime_path(). '/workerman.pid';\n\r";
		$register .= "Worker::\$stdoutFile = runtime_path(). '/stdout.log';\n\r";
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
        $gateway .= "ini_set('display_errors', 'on');\n\r";
		$gateway .= "\n\r";
		$gateway .= "date_default_timezone_set('Asia/Shanghai');\n\r";
		$gateway .= "\n\r";
		$gateway .= "require_once __DIR__ . '/../vendor/autoload.php';\n\r";
		$gateway .= "require_once __DIR__ . '/../support/helpers.php';\n\r";
		$gateway .= "\n\r";
		$gateway .= "use Workerman\Worker;\n\r";
		$gateway .= "use GatewayWorker\Gateway;\n\r";
		$gateway .= "use support\bootstrap\Config;\n\r";
		$gateway .= "\n\r";
		$gateway .= "load_files(app_path());\n\r";
		$gateway .= "load_files(bootstrap_path());\n\r";
		$gateway .= "load_files(extend_path());\n\r";
		$gateway .= "Config::load(config_path());\n\r";
		$gateway .= "\n\r";
		$gateway .= "if (!is_dir(runtime_path())) {\n\r";
		$gateway .= "    mkdir(runtime_path(), 0777, true);\n\r";
		$gateway .= "}\n\r";
		$gateway .= "Worker::\$logFile    = runtime_path(). '/workerman.log';\n\r";
		$gateway .= "Worker::\$pidFile    = runtime_path(). '/workerman.pid';\n\r";
		$gateway .= "Worker::\$stdoutFile = runtime_path(). '/stdout.log';\n\r";
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
        $bussiness .= "ini_set('display_errors', 'on');\n\r";
		$bussiness .= "\n\r";
		$bussiness .= "date_default_timezone_set('Asia/Shanghai');\n\r";
		$bussiness .= "\n\r";
		$bussiness .= "require_once __DIR__ . '/../vendor/autoload.php';\n\r";
		$bussiness .= "require_once __DIR__ . '/../support/helpers.php';\n\r";
		$bussiness .= "\n\r";
		$bussiness .= "use Workerman\Worker;\n\r";
		$bussiness .= "use GatewayWorker\BusinessWorker;\n\r";
		$bussiness .= "use support\bootstrap\Config;\n\r";
		$bussiness .= "\n\r";
		$bussiness .= "load_files(app_path());\n\r";
		$bussiness .= "load_files(bootstrap_path());\n\r";
		$bussiness .= "load_files(extend_path());\n\r";
		$bussiness .= "Config::load(config_path());\n\r";
		$bussiness .= "\n\r";
		$bussiness .= "if (!is_dir(runtime_path())) {\n\r";
		$bussiness .= "    mkdir(runtime_path(), 0777, true);\n\r";
		$bussiness .= "}\n\r";
		$bussiness .= "Worker::\$logFile    = runtime_path(). '/workerman.log';\n\r";
		$bussiness .= "Worker::\$pidFile    = runtime_path(). '/workerman.pid';\n\r";
		$bussiness .= "Worker::\$stdoutFile = runtime_path(). '/stdout.log';\n\r";
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

$ok = file_put_contents(base_path() . DS . "start.bat", $bat);
if (!$ok) {
    exit("Failed to create file");
}