<?php

namespace support\bootstrap;

use Workerman\lib\Timer;

/**
 * 加载定时任务
 *
 * @Author    HSK
 * @DateTime  2020-10-22 17:19:43
 */
class LoadTimer
{
    /**
     * 当前使用进程
     *
     * @var int
     */
    protected static $worker_id = 0;

    /**
     * 加载定时任务
     *
     * @Author    HSK
     * @DateTime  2020-10-22 17:19:57
     *
     * @param string $worker
     * @param bool $type
     *
     * @return void
     */
    public static function load($worker = '', $type = false)
    {
        $worker_name  = parse_name($worker->name, 1);
        $worker_count = $worker->count;

        foreach (glob(timer_path() . DS . $worker_name . '/*.php') as $task => $file) {
            $basename = basename($file, '.php');
            $class    = "\\App\\Timer\\{$worker_name}\\{$basename}";

            if (!$class::$run) {
                continue;
            }

            if ($type == false) {
                $timer_id = Timer::add($class::$interval, [$class, 'init'], [&$timer_id, &$worker], $class::$persistent);
                unset($timer_id);
            } else {
                if ($worker->id == self::$worker_id) {
                    $timer_id = Timer::add($class::$interval, [$class, 'init'], [&$timer_id, &$worker], $class::$persistent);
                    unset($timer_id);
                }

                if (self::$worker_id == $worker_count - 1) {
                    self::$worker_id = 0;
                } else {
                    self::$worker_id++;
                }
            }
        }
    }
}
