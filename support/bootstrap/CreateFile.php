<?php

namespace support\bootstrap;

use Exception;

/**
 * 文件生成
 *
 * @Author    HSK
 * @DateTime  2020-10-15 14:49:20
 */
class CreateFile
{
    /**
     * 按照模版创建文件
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:44:59
     *
     * @param string $namespace
     * @param string $type
     *
     * @return void
     */
    public static function create($namespace = '', $type = '')
    {
        if (empty($namespace) || empty($type)) {
            throw new Exception("File creation, parameter error");
        }

        if (!in_array($type, ['WorkerMan', 'GatewayWorker', 'Async'])) {
            throw new Exception("File creation, parameter error");
        }

        list($null, $base, $callback, $process, $file) = explode('\\', $namespace);

        if (file_exists(__DIR__ . "/Template/" . $file . $type . ".temp")) {
            if (!is_dir(callback_path() . DS . $process)) {
                mkdir(callback_path() . DS . $process, 0777, true);
            }

            if (!file_exists(callback_path() . DS . $process . DS . $file . ".php")) {
                $ok = file_put_contents(callback_path() . DS . $process . DS . $file . ".php", self::Template($file . $type, $process));
                if (!$ok) {
                    throw new Exception("Failed to create file");
                }
            }

            if ($file == 'onWorkerStart' && in_array($type, ['WorkerMan', 'GatewayWorker'])) {
                if (!is_dir(timer_path() . DS . $process)) {
                    mkdir(timer_path() . DS . $process, 0777, true);
                }

                if (!file_exists(timer_path() . DS . $process . DS . "Test.php")) {
                    @file_put_contents(timer_path() . DS . $process . DS . "Test.php", self::Template("Timer", $process));
                }

                if (!is_dir(crontab_path() . DS . $process)) {
                    mkdir(crontab_path() . DS . $process, 0777, true);
                }

                if (!file_exists(crontab_path() . DS . $process . DS . "Test.php")) {
                    @file_put_contents(crontab_path() . DS . $process . DS . "Test.php", self::Template("Crontab", $process));
                }
            }

            if ($file == 'onMessage' && in_array($type, ['WorkerMan', 'GatewayWorker'])) {
                if (!is_dir(message_path() . DS . $process)) {
                    mkdir(message_path() . DS . $process, 0777, true);
                }

                if (!file_exists(message_path() . DS . $process . DS . "Index.php")) {
                    @file_put_contents(message_path() . DS . $process . DS . "Index.php", self::Template("Message" . $type, $process));
                }
            }
        }

        load_files(app_path());
    }

    /**
     * 创建 GatewayWorker 业务处理类
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:44:43
     *
     * @return void
     */
    public static function Events()
    {
        if (!is_dir(callback_path())) {
            mkdir(callback_path(), 0777, true);
        }

        if (!file_exists(callback_path() . DS . "Events.php")) {
            $ok = file_put_contents(callback_path() . DS . "Events.php", self::Template("Events"));
            if (!$ok) {
                throw new Exception("Failed to create file");
            }
        }

        load_files(app_path());
    }

    /**
     * 获取模版文件内容
     *
     * @Author    HSK
     * @DateTime  2020-11-01 16:26:39
     *
     * @param string $temp
     * @param string $process
     *
     * @return void
     */
    protected static function Template($temp, $process = '')
    {
        $str = file_get_contents(__DIR__ . "/Template/{$temp}.temp");

        if (!empty($process)) {
            $str = str_replace("{_process_}", $process, $str);
        }

        return $str;
    }
}
