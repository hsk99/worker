<?php

namespace support\bootstrap;

use support\bootstrap\Redis;

/**
 * 队列
 *
 * @Author    HSK
 * @DateTime  2021-05-17 22:54:12
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
     * @DateTime  2021-05-17 22:55:30
     *
     * @param string $redis_name
     * @param string $queue
     * @param mixed $data
     * @param integer $delay
     *
     * @return boolean
     */
    public static function enter(string $redis_name, string $queue, mixed $data, int $delay = 0): bool
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
     * @DateTime  2021-05-17 22:56:27
     *
     * @param string $redis_name
     * @param string $queue
     *
     * @return mixed
     */
    public static function out(string $redis_name, string $queue)
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
