<?php

use support\base\Config;

define('BASE_PATH', realpath(__DIR__ . '/../'));

/**
 * @return string
 */
function base_path(): string
{
    return BASE_PATH;
}

/**
 * @return string
 */
function app_path(): string
{
    return BASE_PATH . DIRECTORY_SEPARATOR . 'app';
}

/**
 * @return string
 */
function config_path(): string
{
    return BASE_PATH . DIRECTORY_SEPARATOR . 'config';
}

/**
 * @return string
 */
function runtime_path(): string
{
    return BASE_PATH . DIRECTORY_SEPARATOR . 'runtime';
}

/**
 * @return string
 */
function public_path(): string
{
    return BASE_PATH . DIRECTORY_SEPARATOR . 'public';
}

/**
 * @param null $key
 * @param null $default
 *
 * @return mixed
 */
function config($key = null, $default = null)
{
    return Config::get($key, $default);
}

/**
 * @param $worker
 * @param $class
 * 
 * @return void
 */
function worker_bind($worker, $class)
{
    $callbackMap = [
        'onWorkerReload',
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWebSocketConnect'
    ];
    foreach ($callbackMap as $name) {
        if (method_exists($class, $name)) {
            $worker->$name = [$class, $name];
        }
    }
    if (method_exists($class, 'onWorkerStart')) {
        call_user_func([$class, 'onWorkerStart'], $worker);
    }
}

/**
 * 字符串命名风格转换
 *
 * @Author    HSK
 * @DateTime  2021-10-21 15:07:16
 *
 * @param string $name
 * @param integer $type
 * @param boolean $ucfirst
 *
 * @return string
 */
function parse_name($name, $type = 0, $ucfirst = true): string
{
    if ($type) {
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name);

        return $ucfirst ? ucfirst($name) : lcfirst($name);
    }

    return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
}
