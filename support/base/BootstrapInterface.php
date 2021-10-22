<?php

namespace support\base;

use Workerman\Worker;
use GatewayWorker\BusinessWorker;

interface BootstrapInterface
{
    /**
     * onWorkerStart
     *
     * @param Worker|BusinessWorker $worker
     * @return mixed
     */
    public static function start($worker);
}
