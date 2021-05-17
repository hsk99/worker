<?php

namespace support\bootstrap;

use Monolog\Logger;

/**
 * 日志
 *
 * @Author    HSK
 * @DateTime  2021-05-17 22:50:58
 */
class Log
{
    /**
     * debug 通道
     *
     * @var array
     */
    protected static $_debug = [];

    /**
     * debug 日志单独存储
     *
     * @var array
     */
    protected static $_debugMethods = [
        'debug'     => Logger::DEBUG,
        'info'      => Logger::INFO,
        'notice'    => Logger::NOTICE,
        'warning'   => Logger::WARNING,
        'error'     => Logger::ERROR,
        'critical'  => Logger::CRITICAL,
        'alert'     => Logger::ALERT,
        'emergency' => Logger::EMERGENCY
    ];

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
     * @DateTime  2021-05-17 22:52:21
     *
     * @param object $worker
     *
     * @return void
     */
    public static function start(object $worker)
    {
        // debug
        $worker_name = parse_name($worker->name, 1);
        $logger      = new Logger($worker_name . '_debug');
        $formatter   = new \support\bootstrap\LogFormatter\DebugFormatter($worker);
        foreach (self::$_debugMethods as $method => $level) {
            $handler = new \Monolog\Handler\RotatingFileHandler(runtime_path() . "/debug/{$worker_name}/{$method}.log", 0, $level, false);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            static::$_debug[$method] = $logger;
        }

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
     * @DateTime  2021-05-17 22:52:48
     *
     * @param string $name
     *
     * @return object
     */
    public static function channel(string $name = 'default'): object
    {
        return static::$_instance[$name] ?? null;
    }

    /**
     * 使用debug通道
     *
     * @Author    HSK
     * @DateTime  2021-05-17 22:53:42
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (static::$_debug[$name]) {
            return static::$_debug[$name]->{$name}(...$arguments);
        } else {
            return false;
        }
    }
}
