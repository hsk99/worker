<?php

namespace support\bootstrap;

use support\base\BootstrapInterface;
use Workerman\Worker;
use GatewayWorker\BusinessWorker;
use Psr\Container\ContainerInterface;

/**
 * Class Container
 * @package support
 * @method static mixed get($name)
 * @method static mixed make($name, array $parameters)
 * @method static bool has($name)
 */
class Container implements BootstrapInterface
{
    /**
     * @var ContainerInterface
     */
    protected static $_instance = null;

    /**
     * @param Worker|BusinessWorker $worker
     * @return void
     */
    public static function start($worker)
    {
        static::$_instance = include config_path() . '/container.php';
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::$_instance->{$name}(...$arguments);
    }

    /**
     * instance
     * @return
     */
    public static function instance()
    {
        return static::$_instance;
    }
}
