<?php

use support\bootstrap\Config;

define('BASE_PATH', realpath(__DIR__ . '/../'));
define('DS', DIRECTORY_SEPARATOR);

/**
 * 项目目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function base_path(): string
{
    return BASE_PATH;
}

/**
 * 业务目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function app_path(): string
{
    return BASE_PATH . DS . 'App';
}

/**
 * 回调函数目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function callback_path(): string
{
    return BASE_PATH . DS . 'App' . DS . 'Callback';
}

/**
 * 接收数据处理目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function message_path(): string
{
    return BASE_PATH . DS . 'App' . DS . 'Message';
}

/**
 * 定时器目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function timer_path(): string
{
    return BASE_PATH . DS . 'App' . DS . 'Timer';
}

/**
 * 定时任务目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function crontab_path(): string
{
    return BASE_PATH . DS . 'App' . DS . 'Crontab';
}

/**
 * 配置文件目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function config_path(): string
{
    return BASE_PATH . DS . 'config';
}

/**
 * 引导文件目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function bootstrap_path(): string
{
    return BASE_PATH . DS . 'support' . DS . 'bootstrap';
}

/**
 * 拓展文件目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function extend_path(): string
{
    return BASE_PATH . DS . 'support' . DS . 'extend';
}

/**
 * 日志缓存目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function runtime_path(): string
{
    return BASE_PATH . DS . 'runtime';
}

/**
 * 自定义协议目录
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:38:44
 *
 * @return string
 */
function protocols_path(): string
{
    return BASE_PATH . DS . 'Protocols';
}

/**
 * 加载文件
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:40:28
 *
 * @param string $path
 *
 * @return void
 */
function load_files(string $path)
{
    if (empty($path) || !is_dir($path)) {
        return;
    }

    $dir          = realpath($path);
    $dir_iterator = new RecursiveDirectoryIterator($dir);
    $iterator     = new RecursiveIteratorIterator($dir_iterator);
    foreach ($iterator as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
            if (!in_array($file->getPathName(), get_included_files())) {
                include $file->getPathName();
            }
        }
    }
}

/**
 * 数据转JSON
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:41:44
 *
 * @param array|object|string $data
 *
 * @return string
 */
function json($data): string
{
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * 字符串命名风格转换
 * type 0 将 Java 风格转换为 C 的风格 1 将 C 风格转换为 Java 的风格
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:42:50
 *
 * @param string $name
 * @param integer $type
 * @param boolean $ucfirst
 *
 * @return string
 */
function parse_name(string $name, int $type = 0, bool $ucfirst = true): string
{
    if ($type) {
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name);

        return $ucfirst ? ucfirst($name) : lcfirst($name);
    }

    return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
}

/**
 * 获取配置参数
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:48:26
 *
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
 * 循环删除目录和文件
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:51:11
 *
 * @param string $dir_name
 *
 * @return boolean
 */
function delete_dir_file(string $dir_name): bool
{
    $result = false;
    if (is_dir($dir_name)) {
        if ($handle = opendir($dir_name)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dir_name . DS . $item)) {
                        delete_dir_file($dir_name . DS . $item);
                    } else {
                        unlink($dir_name . DS . $item);
                    }
                }
            }
            closedir($handle);
            if (rmdir($dir_name)) {
                $result = true;
            }
        }
    }

    return $result;
}

/**
 * 获取CPU个数
 *
 * @Author    HSK
 * @DateTime  2021-05-10 16:51:24
 *
 * @return integer
 */
function cpu_count(): int
{
    if (strtolower(PHP_OS) === 'darwin') {
        $count = shell_exec('sysctl -n machdep.cpu.core_count');
    } else {
        $count = shell_exec('nproc');
    }

    $count = (int)$count > 0 ? (int)$count : 4;

    return $count;
}
