<?php

namespace support\bootstrap;

use Workerman\lib\Timer;

class LoadTimer
{
    /**
     * 当前使用进程
     * @var integer
     */
    protected static $worker_id = 0;

    /**
     * @method 加载定时任务
     *  
     * @param  string  $worker [description]
     * @param  boolean $type   [description]
     * @return [type]          [description]
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
                $timer_id = Timer::add($class::$interval, [$class, 'init'], [&$timer_id], $class::$persistent);
                unset($timer_id);
            } else {
                if ($worker->id == self::$worker_id) {
                    $timer_id = Timer::add($class::$interval, [$class, 'init'], [&$timer_id], $class::$persistent);
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
