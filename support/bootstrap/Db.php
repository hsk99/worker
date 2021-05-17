<?php

namespace support\bootstrap;

use Exception;
use Workerman\MySQL\Connection;

/**
 * 数据库
 *
 * @Author    HSK
 * @DateTime  2021-05-17 22:48:03
 */
class Db
{
    /**
     * MySql 连接信息
     *
     * @var array
     */
    protected static $_connection = [];

    /**
     * 连接数据库
     *
     * @Author    HSK
     * @DateTime  2021-05-17 22:49:21
     *
     * @return void
     */
    public static function connect()
    {
        $mysql_config = config('mysql');

        foreach ($mysql_config as $name => $config) {
            if (!$config['connect']) {
                continue;
            }

            if (empty($config['host']) || empty($config['user']) || empty($config['dbname'])) {
                throw new Exception("MySql connection information is incomplete");
                continue;
            }

            try {
                $connection = new Connection($config['host'], $config['port'], $config['user'], $config['password'], $config['dbname']);
                self::$_connection[$name] = $connection;
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage());
            }
        }
    }

    /**
     * 获取MySql连接信息
     *
     * @Author    HSK
     * @DateTime  2021-05-17 22:50:02
     *
     * @param string $db_name
     *
     * @return object
     */
    public static function get(string $db_name): object
    {
        if (empty($db_name)) {
            throw new Exception("Parameter error");
        }

        if (empty(self::$_connection[$db_name])) {
            throw new Exception("{$db_name} No connection information");
        }

        return self::$_connection[$db_name];
    }
}
