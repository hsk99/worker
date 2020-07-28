<?php 

use support\bootstrap\Config;

define('BASE_PATH', realpath(__DIR__ . '/../'));
define('DS', DIRECTORY_SEPARATOR);

/**
 * @method 项目目录
 *
 * @return [type]    [description]
 */
function base_path()
{
    return BASE_PATH;
}

/**
 * @method 业务目录
 *
 * @return [type]        [description]
 */
function app_path()
{
    return BASE_PATH . DS . 'App';
}

/**
 * @method 回调函数目录
 *
 * @return [type]        [description]
 */
function callback_path()
{
    return BASE_PATH . DS . 'App' . DS . 'Callback';
}

/**
 * @method 接收数据处理目录
 *
 * @return [type]       [description]
 */
function message_path()
{
    return BASE_PATH . DS . 'App' . DS . 'Message';
}

/**
 * @method 定时任务目录
 *
 * @return [type]     [description]
 */
function timer_path()
{
    return BASE_PATH . DS . 'App' . DS . 'Timer';
}

/**
 * @method 配置文件目录
 *
 * @return [type]      [description]
 */
function config_path()
{
    return BASE_PATH . DS . 'config';
}

/**
 * @method 引导文件目录
 *
 * @return [type]      [description]
 */
function bootstrap_path()
{
    return BASE_PATH . DS . 'support' . DS . 'bootstrap';
}

/**
 * @method 拓展文件目录
 *
 * @return [type]      [description]
 */
function extend_path()
{
    return BASE_PATH . DS . 'support' . DS . 'extend';
}

/**
 * @method 日志缓存目录
 *
 * @return [type]       [description]
 */
function runtime_path()
{
    return BASE_PATH . DS . 'runtime';
}

/**
 * @method 加载文件
 *
 * @param  string $path [description]
 * @return [type]       [description]
 */
function load_files ($path = '')
{
    if (empty($path)) {
        return;
    }

    $dir          = realpath($path);
    $dir_iterator = new RecursiveDirectoryIterator($dir);
    $iterator     = new RecursiveIteratorIterator($dir_iterator);
    foreach ($iterator as $file)
    {
        if(pathinfo($file, PATHINFO_EXTENSION) == 'php')
        {
            if (!in_array($file->getPathName(), get_included_files())) {
                include $file->getPathName();
            }
        }
    }
}

/**
 * @method 数据转JSON
 *  
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
function json ($data)
{
	return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/**
 * @method 字符串命名风格转换
 * type 0 将 Java 风格转换为 C 的风格 1 将 C 风格转换为 Java 的风格
 *
 * @param  [type]     $name    [字符串]
 * @param  integer    $type    [转换类型]
 * @param  boolean    $ucfirst [首字母是否大写（驼峰规则）]
 * @return [type]              [description]
 */
function parse_name($name, $type = 0, $ucfirst = true)
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
 * @method 获取配置参数
 *  
 * @param  [type] $key     [description]
 * @param  [type] $default [description]
 * @return [type]          [description]
 */
function config ($key = null, $default = null)
{
    return Config::get($key, $default);
}

/**
 * @method 循环删除目录和文件
 *
 * @param  [type]          $dir_name [description]
 * @return [type]                    [description]
 */
function delete_dir_file ($dir_name)
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