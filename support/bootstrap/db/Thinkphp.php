<?php

namespace support\bootstrap\db;

use support\base\BootstrapInterface;
use Workerman\Worker;
use GatewayWorker\BusinessWorker;
use think\facade\Db;
use support\bootstrap\Log;

class Thinkphp implements BootstrapInterface
{
    /**
     * 进程启动调用
     *
     * @Author    HSK
     * @DateTime  2021-09-23 11:36:11
     *
     * @param Worker|BusinessWorker $worker
     *
     * @return void
     */
    public static function start($worker)
    {
        // 初始化数据库
        Db::setConfig(config('database'));

        // 监听SQL，并记录日志
        Db::listen(function ($sql, $runtime, $master) {
            $time = microtime(true);

            if ($sql === 'select 1 limit 1') {
                return;
            }

            $sqlLog = [
                'time'     => date('Y-m-d H:i:s.', $time) . substr($time, 11),   // 请求时间（包含毫秒时间）
                'channel'  => 'sql',                                             // 日志通道
                'level'    => 'DEBUG',                                           // 日志等级
                'message'  => '',                                                // 描述
                'sql'      => $sql,                                              // SQL语句
                'run_time' => $runtime,                                          // 运行时长
                'master'   => $master,                                           // 主从标识
            ];

            Log::channel('sql')->debug('', $sqlLog);
        });
    }
}
