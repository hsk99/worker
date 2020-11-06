<?php

namespace support\bootstrap;

class Config
{
    /**
     * 记录信息
     *
     * @var array
     */
    protected static $_config = [];

    /**
     * 记录
     *
     * @Author    HSK
     * @DateTime  2020-10-22 17:20:17
     *
     * @param [type] $config_path
     * @param array $exclude_file
     *
     * @return void
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
     * 读取
     *
     * @Author    HSK
     * @DateTime  2020-10-22 17:20:24
     *
     * @param [type] $key
     * @param [type] $default
     *
     * @return void
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
     * 刷新
     *
     * @Author    HSK
     * @DateTime  2020-10-22 17:20:30
     *
     * @param [type] $config_path
     * @param array $exclude_file
     *
     * @return void
     */
    public static function reload($config_path, $exclude_file = [])
    {
        static::$_config = [];
        static::load($config_path, $exclude_file);
    }
}
