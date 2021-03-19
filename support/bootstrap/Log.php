<?php

namespace support\bootstrap;

use Monolog\Logger;

/**
 * 日志
 *
 * @Author    HSK
 * @DateTime  2021-02-23 14:14:12
 */
class Log
{
    /**
     * debug通道
     *
     * @var [type]
     */
    protected static $_debug;

    /**
     * 自定义通道
     *
     * @var array
     */
    protected static $_instance = [];

    /**
     * 开启通道
     *
     * @Author    HSK
     * @DateTime  2021-02-23 14:15:24
     *
     * @param [type] $worker
     *
     * @return void
     */
    public static function start($worker)
    {
        // debug
        $worker_name = parse_name($worker->name, 1);
        $logger      = static::$_debug = new Logger($worker_name . '_debug');
        $handler     = new \Monolog\Handler\RotatingFileHandler(runtime_path() . '/debug/' . $worker_name . '/debug.log');
        $formatter   = new \support\bootstrap\LogFormatter\DebugFormatter($worker);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        // 自定义
        $configs = config('log', []);
        foreach ($configs as $channel => $config) {
            $logger = static::$_instance[$channel] = new Logger($channel);
            foreach ($config['handlers'] as $handler_config) {
                $handler = new $handler_config['class'](...\array_values($handler_config['constructor']));
                if (isset($handler_config['formatter'])) {
                    $formatter = new $handler_config['formatter']['class'](...\array_values($handler_config['formatter']['constructor']));
                    $handler->setFormatter($formatter);
                }
                $logger->pushHandler($handler);
            }
        }
    }

    /**
     * 获取通道实例
     *
     * @Author    HSK
     * @DateTime  2021-02-23 14:16:09
     *
     * @param string $name
     *
     * @return void
     */
    public static function channel($name = 'default')
    {
        return static::$_instance[$name] ?? null;
    }

    /**
     * 使用debug通道
     *
     * @Author    HSK
     * @DateTime  2021-02-23 14:16:32
     *
     * @param string $name
     * @param array $arguments
     *
     * @return void
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::$_debug->{$name}(...$arguments);
    }
}
