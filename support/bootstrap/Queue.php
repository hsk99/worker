<?php

namespace support\bootstrap;

use support\bootstrap\Redis;

/**
 * 队列
 *
 * @Author    HSK
 * @DateTime  2020-11-05 23:13:18
 */
class Queue
{
    /**
     * 排队等待消费
     */
    const QUEUE_WAITING = 'redis-queue-waiting';

    /**
     * 消费延迟排队
     */
    const QUEUE_DELAYED = 'redis-queue-delayed';

    /**
     * 入列
     *
     * @Author    HSK
     * @DateTime  2020-11-05 22:30:40
     *
     * @param [type] $redis_name
     * @param [type] $queue
     * @param [type] $data
     * @param int $delay
     *
     * @return void
     */
    public static function enter($redis_name, $queue, $data, $delay = 0)
    {
        try {
            $redis = Redis::get($redis_name);

            $now = time();

            $package_str = json_encode([
                'id'       => rand(),
                'time'     => $now,
                'delay'    => 0,
                'attempts' => 0,
                'queue'    => $queue,
                'data'     => $data
            ]);

            if ($delay) {
                return $redis->zAdd(static::QUEUE_DELAYED, $now + $delay, $package_str);
            }

            return $redis->lPush(static::QUEUE_WAITING . $queue, $package_str);
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * 出列
     *
     * @Author    HSK
     * @DateTime  2020-11-05 22:37:19
     *
     * @param [type] $redis_name
     * @param [type] $queue
     *
     * @return void
     */
    public static function out($redis_name, $queue)
    {
        try {
            $redis = Redis::get($redis_name);

            if ($redis->lLen(static::QUEUE_WAITING . $queue) == 0) {
                return false;
            }

            $result = $redis->rPop(static::QUEUE_WAITING . $queue);

            if (empty($result)) {
                return false;
            };

            $data = json_decode($result, true);

            return $data['data'];
        } catch (\Throwable $th) {
            return false;
        }
    }
}
