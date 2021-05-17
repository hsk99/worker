<?php

namespace support\bootstrap;

use Workerman\lib\Timer;
use Workerman\Crontab\Crontab;

/**
 * 加载任务
 *
 * @Author    HSK
 * @DateTime  2021-05-17 22:50:25
 */
class Load
{
    /**
     * 加载定时器
     *
     * @Author    HSK
     * @DateTime  2021-05-17 22:50:38
     *
     * @param object $worker
     *
     * @return void
     */
    public static function timer(object $worker)
    {
        $worker_name = parse_name($worker->name, 1);

        foreach (glob(timer_path() . DS . $worker_name . '/*.php') as $task => $file) {
            $basename = basename($file, '.php');
            $class    = "\\App\\Timer\\{$worker_name}\\{$basename}";

            if (!$class::$run) {
                continue;
            }

            $timer_id = Timer::add($class::$interval, [$class, 'init'], [&$timer_id, &$worker], $class::$persistent);
            unset($timer_id);
        }
    }

    /**
     * 加载定时任务
     *
     * @Author    HSK
     * @DateTime  2021-05-17 22:50:47
     *
     * @param object $worker
     *
     * @return void
     */
    public static function crontab(object $worker)
    {
        $worker_name = parse_name($worker->name, 1);

        foreach (glob(crontab_path() . DS . $worker_name . '/*.php') as $task => $file) {
            $basename = basename($file, '.php');
            $class    = "\\App\\Crontab\\{$worker_name}\\{$basename}";

            if (!$class::$run || empty($class::$rule)) {
                continue;
            }

            $crontab = new Crontab($class::$rule, function () use (&$crontab, &$class, &$worker) {
                $class::init($crontab, $worker);
            });
            unset($crontab);
        }
    }
}
