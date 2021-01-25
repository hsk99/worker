<?php

namespace support\bootstrap;

use Exception;
use Predis\Client;

class Redis
{
    /**
     * Redis连接信息
     *
     * @var array
     */
    protected static $_connection = [];

    /**
     * 连接Redis
     *
     * @Author    HSK
     * @DateTime  2020-10-22 16:35:10
     *
     * @return void
     */
    public static function connect()
    {
        $redis_config = config('redis');

        foreach ($redis_config as $name => $config) {
            if (!$config['connect']) {
                continue;
            }

            if (empty($config['host']) || empty($config['port']) || empty($config['password'])) {
                throw new Exception("Redis connection information is incomplete");
                continue;
            }

            try {
                $connection = new Client();

                $connection->connect($config['host'], $config['port']);
                $connection->auth($config['password']);

                self::$_connection[$name] = $connection;
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage());
            }
        }
    }

    /**
     * 获取Redis连接信息
     *
     * @Author    HSK
     * @DateTime  2020-10-22 16:35:19
     *
     * @param string $redis_name
     *
     * @return void
     */
    public static function get($redis_name = '')
    {
        if (empty($redis_name)) {
            throw new Exception("Parameter error");
        }

        if (empty(self::$_connection[$redis_name])) {
            throw new Exception("{$redis_name} No connection information");
        }

        return self::$_connection[$redis_name];
    }
}
