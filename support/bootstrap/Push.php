<?php

namespace support\bootstrap;

use GatewayClient\Gateway;

class Push
{
    /**
     * GatewayWorker 注册地址
     * @var string
     */
    public static $register = '';

    /**
     * 内部子进程地址
     * @var string
     */
    public static $internal = '';

    /**
     * @method 执行推送
     *
     * @param  string $data [description]
     * @return [type]       [description]
     */
    public static function send ($data = '', $type = 'all', $value = '')
    {
        if (!empty(self::$register)) {
            self::register($data, $type, $value);
        }

        if (!empty(self::$internal)) {
            self::internal($data);
        }
    }

    /**
     * @method 使用 GatewayClient 推送
     *
     * @param  string   $data  [description]
     * @param  string   $type  [description]
     * @param  string   $value [description]
     * @return [type]          [description]
     */
    protected static function register ($data = '', $type = 'all', $value = '')
    {
        try {
            Gateway::$registerAddress  = self::$register;

            switch ($type) {
                case 'uid':
                    Gateway::sendToUid($value, $data);
                    break;
                case 'client':
                    Gateway::sendToClient($value, $data);
                    break;
                case 'group':
                    Gateway::sendToGroup($value, $data);
                    break;
                case 'all':
                    Gateway::sendToAll($data);
                    break;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @method 使用 内部子进程 执行推送
     *
     * @param  string   $data [description]
     * @return [type]         [description]
     */
    protected static function internal ($data = '')
    {
        $client = @stream_socket_client(self::$internal, $errno, $errmsg);
        if (!$client) {
            return false;
        } else {
            fwrite($client, $data);
            fclose($client);
            return true;
        }
    }
}
