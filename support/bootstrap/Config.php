<?php

namespace support\bootstrap;

class Config
{
    /**
     * 记录信息
     * @var array
     */
    protected static $_config = [];

    /**
     * @method 记录
     *  
     * @param  [type] $config_path  [description]
     * @param  array  $exclude_file [description]
     * @return [type]               [description]
     */
    public static function load($config_path, $exclude_file = [])
    {
        foreach (\glob($config_path . '/*.php') as $file) {
            $basename = \basename($file, '.php');
            if (\in_array($basename, $exclude_file)) {
                continue;
            }
            $config = include $file;
            static::$_config[$basename] = $config;
        }
    }

    /**
     * @method 读取
     *  
     * @param  [type] $key     [description]
     * @param  [type] $default [description]
     * @return [type]          [description]
     */
    public static function get($key = null, $default = null)
    {
        if ($key === null) {
            return static::$_config;
        }
        $key_array = \explode('.', $key);
        $value = static::$_config;
        foreach ($key_array as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }

    /**
     * @method 刷新
     *  
     * @param  [type] $config_path  [description]
     * @param  array  $exclude_file [description]
     * @return [type]               [description]
     */
    public static function reload($config_path, $exclude_file = [])
    {
        static::$_config = [];
        static::load($config_path, $exclude_file);
    }
}
