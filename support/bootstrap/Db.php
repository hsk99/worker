<?php

namespace support\bootstrap;

use \Exception;
use \Workerman\MySQL\Connection;

class Db
{

    /**
     * MySql连接信息
     * @var array
     */
    protected static $_connection = [];

    /**
     * @method 连接数据库
     *
     * @return [type]           [description]
     */
    public static function connect ()
    {
        $mysql_config = config('mysql');
        
        foreach ($mysql_config as $name => $config) {
            if (!$config['connect']) {
                continue;
            }

            if (empty($config['host']) || empty($config['user']) || empty($config['dbname'])) {
                continue;
            }

            $connection = new Connection($config['host'], $config['port'], $config['user'], $config['password'], $config['dbname']);
            self::$_connection[$name] = $connection;
        }
    }

    /**
     * @method 获取MySql连接信息
     *
     * @param  string $db_name [description]
     * @return [type]          [description]
     */
    public static function get ($db_name = '')
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
