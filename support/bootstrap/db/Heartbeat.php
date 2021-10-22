<?php

namespace support\bootstrap\db;

use support\base\BootstrapInterface;
use Workerman\Worker;
use GatewayWorker\BusinessWorker;
use Workerman\Timer;
use support\Db;

class Heartbeat implements BootstrapInterface
{
    /**
     * 进程启动调用
     *
     * @Author    HSK
     * @DateTime  2021-09-23 11:36:24
     *
     * @param Worker|BusinessWorker $worker
     *
     * @return void
     */
    public static function start($worker)
    {
        Timer::add(55, function () {
            Db::query('select 1 limit 1');
        });
    }
}
