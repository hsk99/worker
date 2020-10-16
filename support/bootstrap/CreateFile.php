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

        if (!in_array($type, ['WorkerMan', 'GatewayWorker'])) {
            throw new Exception("File creation, parameter error");
        }

        list($null, $base, $callback, $process, $file) = explode('\\', $namespace);

        if (method_exists("\\support\\bootstrap\\CreateFile", $file . $type)) {
            if (!is_dir(callback_path() . DS . $process)) {
                mkdir(callback_path() . DS . $process, 0777, true);
            }
            if (!file_exists(callback_path() . DS . $process . DS . $file . ".php")) {
                $ok = file_put_contents(callback_path() . DS . $process . DS . $file . ".php", self::{$file . $type}($process));
                if (!$ok) {
                    throw new Exception("Failed to create file");
                }
            }

            if ($file == 'onWorkerStart') {
                if (!is_dir(timer_path() . DS . $process)) {
                    mkdir(timer_path() . DS . $process, 0777, true);
                }
                if (!file_exists(timer_path() . DS . $process . DS . "Test.php")) {
                    @file_put_contents(timer_path() . DS . $process . DS . "Test.php", self::Timer($process));
                }
            }

            if ($file == 'onMessage') {
                if (!is_dir(message_path() . DS . $process)) {
                    mkdir(message_path() . DS . $process, 0777, true);
                }
                if (!file_exists(message_path() . DS . $process . DS . "Index.php")) {
                    @file_put_contents(message_path() . DS . $process . DS . "Index.php", self::{'Message' . $type}($process));
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
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback;\n";
        $str .= "\n";
        $str .= "class Events\n";
        $str .= "{\n";
        $str .= "    protected static \$worker_name;\n";
        $str .= "\n";
        $str .= "    public static function onWorkerStart(\$businessWorker)\n";
        $str .= "    {\n";
        $str .= "        self::\$worker_name = parse_name(\$businessWorker->name, 1);\n";
        $str .= "\n";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWorkerStart::init\")) {\n";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWorkerStart::init\", \$businessWorker);\n";
        $str .= "        }\n";
        $str .= "    }\n";
        $str .= "\n";
        $str .= "    public static function onWorkerStop(\$businessWorker)\n";
        $str .= "    {\n";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWorkerStop::init\")) {\n";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWorkerStop::init\", \$businessWorker);\n";
        $str .= "        }\n";
        $str .= "    }\n";
        $str .= "\n";
        $str .= "    public static function onConnect(\$client_id)\n";
        $str .= "    {\n";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onConnect::init\")) {\n";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onConnect::init\", \$client_id);\n";
        $str .= "        }\n";
        $str .= "    }\n";
        $str .= "\n";
        $str .= "    public static function onWebSocketConnect(\$client_id, \$data)\n";
        $str .= "    {\n";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWebSocketConnect::init\")) {\n";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWebSocketConnect::init\", \$client_id, \$data);\n";
        $str .= "        }\n";
        $str .= "    }\n";
        $str .= "\n";
        $str .= "    public static function onMessage(\$client_id, \$message)\n";
        $str .= "    {\n";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onMessage::init\")) {\n";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onMessage::init\", \$client_id, \$message);\n";
        $str .= "        }\n";
        $str .= "    }\n";
        $str .= "\n";
        $str .= "    public static function onClose(\$client_id)\n";
        $str .= "    {\n";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onClose::init\")) {\n";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onClose::init\", \$client_id);\n";
        $str .= "        }\n";
        $str .= "    }\n";
        $str .= "}\n";

        if (!is_dir(callback_path())) {
            mkdir(callback_path(), 0777, true);
        }
        if (!file_exists(callback_path() . DS . "Events.php")) {
            $ok = file_put_contents(callback_path() . DS . "Events.php", $str);
            if (!$ok) {
                throw new Exception("Failed to create file");
            }
        }

        load_files(app_path());
    }

    /**
     * WorkerMan 的 onWorkerStart 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:45:15
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onWorkerStartWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "use support\bootstrap\Db;\n";
        $str .= "use support\bootstrap\LoadTimer;\n";
        $str .= "\n";
        $str .= "class onWorkerStart\n";
        $str .= "{\n";
        $str .= "    public static function init(\$worker)\n";
        $str .= "    {\n";
        $str .= "        Db::connect();\n";
        $str .= "        LoadTimer::load(\$worker);\n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * WorkerMan 的 onWorkerReload 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:45:24
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onWorkerReloadWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "class onWorkerReload\n";
        $str .= "{\n";
        $str .= "    public static function init(\$worker)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * WorkerMan 的 onConnect 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:45:33
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onConnectWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "class onConnect\n";
        $str .= "{\n";
        $str .= "    public static function init(\$connection)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * WorkerMan 的 onMessage 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:45:42
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onMessageWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "class onMessage\n";
        $str .= "{\n";
        $str .= "    public static function init(\$connection, \$message)\n";
        $str .= "    {\n";
        $str .= "        if (in_array(\$connection->worker->protocol, [\"\\Workerman\\Protocols\\Http\", \"Workerman\\Protocols\\Http\"]))  {\n";
        $str .= "            \$url = \$message->path();\n";
        $str .= "\n";
        $str .= "            if (strpos(\$url, '/') === 0) {\n";
        $str .= "                \$url = substr(\$url, 1, strlen(\$url) -1);\n";
        $str .= "            }\n";
        $str .= "\n";
        $str .= "            \$piece = count(explode('/', \$url));\n";
        $str .= "\n";
        $str .= "            switch (\$piece) {\n";
        $str .= "                case '1':\n";
        $str .= "                    if (\$url === \"\") {\n";
        $str .= "                        \$controller = \$action = parse_name('index', 1);\n";
        $str .= "                    } else {\n";
        $str .= "                        \$controller = \$action = parse_name(\$url, 1);\n";
        $str .= "                    }\n";
        $str .= "                    \$module = \"\";\n";
        $str .= "                    break;\n";
        $str .= "                case '2':\n";
        $str .= "                    list(\$controller, \$action) = explode('/', \$url, 2);\n";
        $str .= "                    \$module     = \"\";\n";
        $str .= "                    \$controller = parse_name(\$controller, 1);\n";
        $str .= "                    \$action     = parse_name(\$action, 1);\n";
        $str .= "                    break;\n";
        $str .= "                case '3':\n";
        $str .= "                    list(\$module, \$controller, \$action) = explode('/', \$url, 3);\n";
        $str .= "                    \$module     = \"\\\\\" . parse_name(\$module, 1);\n";
        $str .= "                    \$controller = parse_name(\$controller, 1);\n";
        $str .= "                    \$action     = parse_name(\$action, 1);\n";
        $str .= "                    break;\n";
        $str .= "                default:\n";
        $str .= "                    \$connection->send(json(['type'=>'error', 'msg'=>'非法操作！']));\n";
        $str .= "                    return;\n";
        $str .= "                    break;\n";
        $str .= "            }\n";
        $str .= "\n";
        $str .= "            if (is_callable(\"\\\App\\\Message\\\\" . $process . "{\$module}\\\{\$controller}::{\$action}\")) {\n";
        $str .= "                call_user_func(\"\\\App\\\Message\\\\" . $process . "{\$module}\\\{\$controller}::{\$action}\", \$connection, \$message);\n";
        $str .= "            } else {\n";
        $str .= "                \$connection->send(json(['type'=>'error', 'msg'=>'非法操作！']));\n";
        $str .= "                return;\n";
        $str .= "            }\n";
        $str .= "        } else {\n";
        $str .= "            \$message_data = json_decode(\$message, true);\n";
        $str .= "            if (empty(\$message_data) || !is_array(\$message_data)) {\n";
        $str .= "                \$connection->send(json(['type' => 'error', 'msg' => '非法操作，传输数据不是JSON格式']));\n";
        $str .= "                return;\n";
        $str .= "            }\n";
        $str .= "\n";
        $str .= "            foreach (\$message_data as \$type => \$data) {\n";
        $str .= "                if (empty(\$data['cmd_sequence'])) {\n";
        $str .= "                    \$return[\$type] = ['code' => 400, 'msg' => '非法操作，指令序列号不存在'];\n";
        $str .= "                    continue;\n";
        $str .= "                }\n";
        $str .= "\n";
        $str .= "                \$piece = count(explode('.', \$type));\n";
        $str .= "                switch (\$piece) {\n";
        $str .= "                    case '1':\n";
        $str .= "                        \$module     = \"\";\n";
        $str .= "                        \$controller = \$action = parse_name(\$type, 1);\n";
        $str .= "                        break;\n";
        $str .= "                    case '2':\n";
        $str .= "                        list(\$controller, \$action) = explode('.', \$type, 2);\n";
        $str .= "                        \$module     = \"\";\n";
        $str .= "                        \$controller = parse_name(\$controller, 1);\n";
        $str .= "                        \$action     = parse_name(\$action, 1);\n";
        $str .= "                        break;\n";
        $str .= "                    case '3':\n";
        $str .= "                        list(\$module, \$controller, \$action) = explode('.', \$type, 3);\n";
        $str .= "                        \$module     = \"\\\\\" . parse_name(\$module, 1);\n";
        $str .= "                        \$controller = parse_name(\$controller, 1);\n";
        $str .= "                        \$action     = parse_name(\$action, 1);\n";
        $str .= "                        break;\n";
        $str .= "                    default:\n";
        $str .= "                        \$module = \$controller = \$action = \"\";\n";
        $str .= "                        break;\n";
        $str .= "                }\n";
        $str .= "\n";
        $str .= "                if (!empty(\$controller) && !empty(\$action) && is_callable(\"\\\App\\\Message\\\\" . $process . "{\$module}\\\{\$controller}::{\$action}\")) {\n";
        $str .= "                    \$result = (\"\\\App\\\Message\\\\" . $process . "{\$module}\\\{\$controller}::{\$action}\")(\$connection, \$data);\n";
        $str .= "                    \$return[\$type] = array_merge(['cmd_sequence' => \$data['cmd_sequence']], \$result);\n";
        $str .= "                } else {\n";
        $str .= "                    \$return[\$type] = ['code' => 400, 'msg' => '非法操作，方法不存在'];\n";
        $str .= "                }\n";
        $str .= "            }\n";
        $str .= "\n";
        $str .= "            \$connection->send(json(\$return));\n";
        $str .= "        }\n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * WorkerMan 的 onClose 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:45:52
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onCloseWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "class onClose\n";
        $str .= "{\n";
        $str .= "    public static function init(\$connection)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * WorkerMan 的 onBufferFull 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:46:13
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onBufferFullWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "class onBufferFull\n";
        $str .= "{\n";
        $str .= "    public static function init(\$connection)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * WorkerMan 的 onBufferDrain 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:46:28
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onBufferDrainWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "class onBufferDrain\n";
        $str .= "{\n";
        $str .= "    public static function init(\$connection)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * WorkerMan 的 onError 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:46:35
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onErrorWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "class onError\n";
        $str .= "{\n";
        $str .= "    public static function init(\$connection, \$code, \$msg)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * WorkerMan 的 onWorkerStop 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:46:50
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onWorkerStopWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "use GatewayWorker\Lib\Gateway;\n";
        $str .= "\n";
        $str .= "class onWorkerStop\n";
        $str .= "{\n";
        $str .= "    public static function init(\$worker)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * GatewayWorker 的 onWorkerStart 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:47:16
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onWorkerStartGatewayWorker($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "use support\bootstrap\Db;\n";
        $str .= "use support\bootstrap\LoadTimer;\n";
        $str .= "\n";
        $str .= "class onWorkerStart\n";
        $str .= "{\n";
        $str .= "    public static function init(\$businessWorker)\n";
        $str .= "    {\n";
        $str .= "        Db::connect();\n";
        $str .= "        LoadTimer::load(\$businessWorker);\n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * GatewayWorker 的 onConnect 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:47:33
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onConnectGatewayWorker($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "use GatewayWorker\Lib\Gateway;\n";
        $str .= "\n";
        $str .= "class onConnect\n";
        $str .= "{\n";
        $str .= "    public static function init(\$client_id)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * GatewayWorker 的 onWebSocketConnect 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:47:42
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onWebSocketConnectGatewayWorker($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "use GatewayWorker\Lib\Gateway;\n";
        $str .= "\n";
        $str .= "class onWebSocketConnect\n";
        $str .= "{\n";
        $str .= "    public static function init(\$client_id, \$data)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * GatewayWorker 的 onMessage 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:47:50
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onMessageGatewayWorker($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "use GatewayWorker\Lib\Gateway;\n";
        $str .= "\n";
        $str .= "class onMessage\n";
        $str .= "{\n";
        $str .= "    public static function init(\$client_id, \$message)\n";
        $str .= "    {\n";
        $str .= "        \$message_data = json_decode(\$message, true);\n";
        $str .= "        if (empty(\$message_data) || !is_array(\$message_data)) {\n";
        $str .= "            Gateway::sendToClient(\$client_id, json(['type' => 'error', 'msg' => '非法操作，传输数据不是JSON格式']));\n";
        $str .= "            return;\n";
        $str .= "        }\n";
        $str .= "\n";
        $str .= "        foreach (\$message_data as \$type => \$data) {\n";
        $str .= "            if (empty(\$data['cmd_sequence'])) {\n";
        $str .= "                \$return[\$type] = ['code' => 400, 'msg' => '非法操作，指令序列号不存在'];\n";
        $str .= "                continue;\n";
        $str .= "            }\n";
        $str .= "\n";
        $str .= "            \$piece = count(explode('.', \$type));\n";
        $str .= "            switch (\$piece) {\n";
        $str .= "                case '1':\n";
        $str .= "                    \$module     = \"\";\n";
        $str .= "                    \$controller = \$action = parse_name(\$type, 1);\n";
        $str .= "                    break;\n";
        $str .= "                case '2':\n";
        $str .= "                    list(\$controller, \$action) = explode('.', \$type, 2);\n";
        $str .= "                    \$module     = \"\";\n";
        $str .= "                    \$controller = parse_name(\$controller, 1);\n";
        $str .= "                    \$action     = parse_name(\$action, 1);\n";
        $str .= "                    break;\n";
        $str .= "                case '3':\n";
        $str .= "                    list(\$module, \$controller, \$action) = explode('.', \$type, 3);\n";
        $str .= "                    \$module     = \"\\\\\" . parse_name(\$module, 1);\n";
        $str .= "                    \$controller = parse_name(\$controller, 1);\n";
        $str .= "                    \$action     = parse_name(\$action, 1);\n";
        $str .= "                    break;\n";
        $str .= "                default:\n";
        $str .= "                    \$module = \$controller = \$action = \"\";\n";
        $str .= "                    break;\n";
        $str .= "            }\n";
        $str .= "\n";
        $str .= "            if (!empty(\$controller) && !empty(\$action) && is_callable(\"\\\App\\\Message\\\\" . $process . "{\$module}\\\{\$controller}::{\$action}\")) {\n";
        $str .= "                \$result = (\"\\\App\\\Message\\\\" . $process . "{\$module}\\\{\$controller}::{\$action}\")(\$client_id, \$data);\n";
        $str .= "                \$return[\$type] = array_merge(['cmd_sequence' => \$data['cmd_sequence']], \$result);\n";
        $str .= "            } else {\n";
        $str .= "                \$return[\$type] = ['code' => 400, 'msg' => '非法操作，方法不存在'];\n";
        $str .= "            }\n";
        $str .= "        }\n";
        $str .= "\n";
        $str .= "        Gateway::sendToClient(\$client_id, json(\$return));\n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * GatewayWorker 的 onClose 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:47:58
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onCloseGatewayWorker($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "use GatewayWorker\Lib\Gateway;\n";
        $str .= "\n";
        $str .= "class onClose\n";
        $str .= "{\n";
        $str .= "    public static function init(\$client_id)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * GatewayWorker 的 onWorkerStop 回调文件模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:48:05
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function onWorkerStopGatewayWorker($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Callback\\" . $process . ";\n";
        $str .= "\n";
        $str .= "use GatewayWorker\Lib\Gateway;\n";
        $str .= "\n";
        $str .= "class onWorkerStop\n";
        $str .= "{\n";
        $str .= "    public static function init(\$businessWorker)\n";
        $str .= "    {\n";
        $str .= "        \n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * 定时器模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:48:15
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function Timer($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Timer\\" . $process . ";\n";
        $str .= "\n";
        $str .= "class Test\n";
        $str .= "{\n";
        $str .= "    /**\n";
        $str .= "     * 是否运行\n";
        $str .= "     * @var boolean\n";
        $str .= "     */\n";
        $str .= "    public static \$run = false;\n";
        $str .= "\n";
        $str .= "    /**\n";
        $str .= "     * 间隔时间\n";
        $str .= "     * @var integer\n";
        $str .= "     */\n";
        $str .= "    public static \$interval = 3;\n";
        $str .= "\n";
        $str .= "    /**\n";
        $str .= "     * 是否是持久的\n";
        $str .= "     * @var boolean\n";
        $str .= "     */\n";
        $str .= "    public static \$persistent = true;\n";
        $str .= "\n";
        $str .= "    /**\n";
        $str .= "     * @method init\n";
        $str .= "     *\n";
        $str .= "     * @param  [type] \$timer_id [定时器ID]\n";
        $str .= "     * @return [type]           [description]\n";
        $str .= "     */\n";
        $str .= "    public static function init(\$timer_id)\n";
        $str .= "    {\n";
        $str .= "        var_dump(\$timer_id);\n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * WorkerMan 接收数据处理模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:48:48
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function MessageWorkerMan($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Message\\" . $process . ";\n";
        $str .= "\n";
        $str .= "class Index\n";
        $str .= "{\n";
        $str .= "    public static function Index(\$connection, \$message)\n";
        $str .= "    {\n";
        $str .= "        if (in_array(\$connection->worker->protocol, [\"\\Workerman\\Protocols\\Http\", \"Workerman\\Protocols\\Http\"])) {\n";
        $str .= "            \$connection->send('Index');\n";
        $str .= "        } else {\n";
        $str .= "            return ['code' => 200, 'msg' => 'success', 'data' => 'Index'];\n";
        $str .= "        }\n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }

    /**
     * GatewayWorker 接收数据处理模版
     *
     * @Author    HSK
     * @DateTime  2020-10-15 14:49:01
     *
     * @param [type] $process
     *
     * @return void
     */
    protected static function MessageGatewayWorker($process)
    {
        $str = "<?php\n";
        $str .= "\n";
        $str .= "namespace App\Message\\" . $process . ";\n";
        $str .= "\n";
        $str .= "use GatewayWorker\Lib\Gateway;\n";
        $str .= "\n";
        $str .= "class Index\n";
        $str .= "{\n";
        $str .= "    public static function Index(\$client_id, \$message_data)\n";
        $str .= "    {\n";
        $str .= "        return ['code' => 200, 'msg' => 'success', 'data' => 'Index'];\n";
        $str .= "    }\n";
        $str .= "}\n";

        return $str;
    }
}
