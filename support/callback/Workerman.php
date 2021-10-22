<?php

namespace support\callback;

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

class Workerman
{
    protected static $handler = null;
    protected static $workerName = '';

    public static function onWorkerStart(Worker $worker)
    {
        self::$workerName = $worker->name;

        $config = config('workerman.' . $worker->name, []);

        if (isset($config['handler'])) {
            self::$handler = $config['handler'];
        }

        if (is_callable(self::$handler . "::onWorkerStart")) {
            call_user_func(self::$handler . "::onWorkerStart", $worker);
        }
    }

    public static function onWorkerStop(Worker $worker)
    {
        if (is_callable(self::$handler . "::onWorkerStop")) {
            call_user_func(self::$handler . "::onWorkerStop", $worker);
        }
    }

    public static function onConnect(TcpConnection $connection)
    {
        if (is_callable(self::$handler . "::onConnect")) {
            call_user_func(self::$handler . "::onConnect", $connection);
        }
    }

    public static function onClose(TcpConnection $connection)
    {
        if (is_callable(self::$handler . "::onClose")) {
            call_user_func(self::$handler . "::onClose", $connection);
        }
    }

    public static function onBufferFull(TcpConnection $connection)
    {
        if (is_callable(self::$handler . "::onBufferFull")) {
            call_user_func(self::$handler . "::onBufferFull", $connection);
        }
    }

    public static function onBufferDrain(TcpConnection $connection)
    {
        if (is_callable(self::$handler . "::onBufferDrain")) {
            call_user_func(self::$handler . "::onBufferDrain", $connection);
        }
    }

    public static function onError(TcpConnection $connection, $code, $msg)
    {
        if (is_callable(self::$handler . "::onError")) {
            call_user_func(self::$handler . "::onError", $connection, $code, $msg);
        }
    }

    public static function onMessage(TcpConnection $connection, $message)
    {
        try {
            if (null === self::$handler) {
                self::processingRequest($connection, $message);
                return;
            }

            if (!is_callable(self::$handler . "::onMessage")) {
                throw new \Exception('system error', 500);
            }

            call_user_func(self::$handler . "::onMessage", $connection, $message);
        } catch (\Throwable $th) {
            $json = [
                'code' => $th->getCode() ?? 500,
                'msg'  => $th->getMessage()
            ];

            $connection->send(json_encode($json, 320));
        }
    }

    protected static function processingRequest($connection, $message)
    {
        if (in_array($connection->worker->protocol, ["\Workerman\Protocols\Http", "Workerman\Protocols\Http"])) {
            self::httpRequest($connection, $message);
        } else {
            self::singleRequest($connection, $message);
        }
    }

    protected static function httpRequest($connection, $message)
    {
        $url = $message->path();

        if (strpos($url, '/') === 0) {
            $url = substr($url, 1, strlen($url) - 1);
        }

        $piece = count(explode('/', $url));

        switch ($piece) {
            case '1':
                if ($url === "") {
                    $controller = parse_name('index', 1);
                    $action     = parse_name('index');
                } else {
                    $controller = parse_name($url, 1);
                    $action     = parse_name($url);
                }
                $module = "";
                break;
            case '2':
                list($controller, $action) = explode('/', $url, 2);
                $module     = "";
                $controller = parse_name($controller, 1);
                $action     = parse_name($action, 1, false);
                break;
            case '3':
                list($module, $controller, $action) = explode('/', $url, 3);
                $module     = "\\" . parse_name($module, 1);
                $controller = parse_name($controller, 1);
                $action     = parse_name($action, 1, false);
                break;
            default:
                $connection->send(json_encode(['type' => 'error', 'msg' => '非法操作！'], 320));
                return;
                break;
        }

        if (is_callable("\\app\\message\\" . self::$workerName . "{$module}\\{$controller}::{$action}")) {
            call_user_func("\\app\\message\\" . self::$workerName . "{$module}\\{$controller}::{$action}", $connection, $message);
        } else {
            $connection->send(json_encode(['type' => 'error', 'msg' => '非法操作！'], 320));
        }
    }

    protected static function singleRequest($connection, $message)
    {
        if (!is_array($message)) {
            $message_data = json_decode($message, true);
            if (empty($message_data) || !is_array($message_data)) {
                $connection->send(json_encode(['type' => 'error', 'msg' => '非法操作，传输数据不是JSON格式'], 320));
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
                $check = ("\\app\\message\\" . self::$workerName . "\\Auth::check")($connection, $message_data, $type, $module, $controller, $action);
                if (!empty($check) && $check['code'] == 400) {
                    $connection->send(json_encode(['type' => 'error', 'msg' => $check['msg']], 320));
                    return;
                }
            }

            $return = ("\\app\\message\\" . self::$workerName . "{$module}\\{$controller}::{$action}")($connection, $message_data);
            if (empty($return)) {
                return;
            }
        } else {
            $return = ['code' => 400, 'msg' => '非法操作，方法不存在'];
        }
        $return['type'] = $type;

        $connection->send(json_encode($return, 320));
    }
}
