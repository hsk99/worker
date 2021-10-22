<?php

namespace support\callback;

use support\base\Config;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Lib\Gateway;

class GatewayWorker
{
    protected static $handler = null;
    protected static $workerName = '';

    public static function onWorkerStart(BusinessWorker $worker)
    {
        Config::reload(config_path());

        self::$workerName = $worker->name;

        $config = config('gateway_worker.' . $worker->name, []);

        if (isset($config['handler'])) {
            self::$handler = $config['handler'];
        }

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

        if (is_callable(self::$handler . "::onWorkerStart")) {
            call_user_func(self::$handler . "::onWorkerStart", $worker);
        }
    }

    public static function onWorkerStop(BusinessWorker $worker)
    {
        if (is_callable(self::$handler . "::onWorkerStop")) {
            call_user_func(self::$handler . "::onWorkerStop", $worker);
        }
    }

    public static function onConnect($client_id)
    {
        if (is_callable(self::$handler . "::onConnect")) {
            call_user_func(self::$handler . "::onConnect", $client_id);
        }
    }

    public static function onClose($client_id)
    {
        if (is_callable(self::$handler . "::onClose")) {
            call_user_func(self::$handler . "::onClose", $client_id);
        }
    }

    public static function onWebSocketConnect($client_id, $data)
    {
        if (is_callable(self::$handler . "::onWebSocketConnect")) {
            call_user_func(self::$handler . "::onWebSocketConnect", $client_id, $data);
        }
    }

    public static function onMessage($client_id, $message)
    {
        try {
            if (null === self::$handler) {
                self::processingRequest($client_id, $message);
                return;
            }

            if (!is_callable(self::$handler . "::onMessage")) {
                throw new \Exception('system error', 500);
            }

            call_user_func(self::$handler . "::onMessage", $client_id, $message);
        } catch (\Throwable $th) {
            $json = [
                'code' => $th->getCode() ?? 500,
                'msg'  => $th->getMessage()
            ];

            Gateway::sendToClient($client_id, json_encode($json, 320));
        }
    }

    protected static function processingRequest($client_id, $message)
    {
        if (!is_array($message)) {
            $message_data = json_decode($message, true);
            if (empty($message_data) || !is_array($message_data)) {
                Gateway::sendToClient($client_id, json_encode(['type' => 'error', 'msg' => '非法操作，传输数据不是JSON格式'], 320));
                return;
            }
        } else {
            $message_data = $message;
        }

        $type  = $message_data['type'];
        $piece = count(explode('.', $type));

        switch ($piece) {
            case '1':
                $module     = "";
                $controller = parse_name($type, 1);
                $action     = parse_name($type, 1, false);
                break;
            case '2':
                list($controller, $action) = explode('.', $type, 2);
                $module     = "";
                $controller = parse_name($controller, 1);
                $action     = parse_name($action, 1, false);
                break;
            case '3':
                list($module, $controller, $action) = explode('.', $type, 3);
                $module     = "\\" . parse_name($module, 1);
                $controller = parse_name($controller, 1);
                $action     = parse_name($action, 1, false);
                break;
            default:
                $module = $controller = $action = "";
                break;
        }

        if (!empty($controller) && !empty($action) && is_callable("\\app\\message\\" . self::$workerName . "{$module}\\{$controller}::{$action}")) {
            if (is_callable("\\app\\message\\" . self::$workerName . "\\Auth::check")) {
                $check = ("\\app\\message\\" . self::$workerName . "\\Auth::check")($client_id, $message_data, $type, $module, $controller, $action);
                if (!empty($check) && $check['code'] == 400) {
                    Gateway::sendToClient($client_id, json_encode(['type' => 'error', 'msg' => $check['msg']], 320));
                    return;
                }
            }

            $result = ("\\app\\message\\" . self::$workerName . "{$module}\\{$controller}::{$action}")($client_id, $message_data);
            if (empty($result)) {
                return;
            }
        } else {
            $result = ['code' => 400, 'msg' => '非法操作，方法不存在'];
        }
        $result['type'] = $type;

        Gateway::sendToClient($client_id, json_encode($result, 320));
    }
}
