<?php

namespace support\bootstrap;

use \Exception;

class CreateFile
{
    /**
     * @method 按照模版创建文件
     *  
     * @param  string $namespace [description]
     * @param  string $type      [description]
     * @return [type]            [description]
     */
    public static function create ($namespace = '', $type = '')
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
            $ok = file_put_contents(callback_path() . DS . $process . DS . $file . ".php", self::{$file . $type}($process));
            if (!$ok) {
                throw new Exception("Failed to create file");
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
                if (!file_exists(message_path() . DS . $process . DS . "Test.php")) {
                    @file_put_contents(message_path() . DS . $process . DS . "Test.php", self::{'Message' . $type}($process));
                }
            }
        }
    }

    /**
     * @method 创建 GatewayWorker 业务处理类
     *  
     */
    public static function Events ()
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback;\n\r";
        $str .= "\n\r";
        $str .= "class Events\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * 进程名称\n\r";
        $str .= "     * @var [type]\n\r";
        $str .= "     */\n\r";
        $str .= "    protected static \$worker_name;\n\r";
        $str .= "    \n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 进程启动时触发\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type]        \$businessWorker [description]\n\r";
        $str .= "     * @return [type]                        [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function onWorkerStart (\$businessWorker)\n\r";
        $str .= "    {\n\r";
        $str .= "        self::\$worker_name = parse_name(\$businessWorker->name, 1);\n\r";
        $str .= "        \n\r";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWorkerStart::init\")) {\n\r";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWorkerStart::init\", \$businessWorker);\n\r";
        $str .= "        }\n\r";
        $str .= "    }\n\r";
        $str .= "    \n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 进程退出时触发\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type]       \$businessWorker [description]\n\r";
        $str .= "     * @return [type]                       [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function onWorkerStop (\$businessWorker)\n\r";
        $str .= "    {\n\r";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWorkerStop::init\")) {\n\r";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWorkerStop::init\", \$businessWorker);\n\r";
        $str .= "        }\n\r";
        $str .= "    }\n\r";
        $str .= "    \n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 客户端连接成功时触发\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type]    \$client_id [description]\n\r";
        $str .= "     * @return [type]               [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function onConnect (\$client_id)\n\r";
        $str .= "    {\n\r";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onConnect::init\")) {\n\r";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onConnect::init\", \$client_id);\n\r";
        $str .= "        }\n\r";
        $str .= "    }\n\r";
        $str .= "    \n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 客户端连接上gateway完成websocket握手时触发\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type]             \$client_id [description]\n\r";
        $str .= "     * @param  [type]             \$data      [description]\n\r";
        $str .= "     * @return [type]                        [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function onWebSocketConnect (\$client_id, \$data)\n\r";
        $str .= "    {\n\r";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWebSocketConnect::init\")) {\n\r";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onWebSocketConnect::init\", \$client_id, \$data);\n\r";
        $str .= "        }\n\r";
        $str .= "    }\n\r";
        $str .= "    \n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 客户端发来数据时触发\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type]    \$client_id [description]\n\r";
        $str .= "     * @param  [type]    \$message   [description]\n\r";
        $str .= "     * @return [type]               [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function onMessage (\$client_id, \$message)\n\r";
        $str .= "    {\n\r";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onMessage::init\")) {\n\r";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onMessage::init\", \$client_id, \$message);\n\r";
        $str .= "        }\n\r";
        $str .= "    }\n\r";
        $str .= "    \n\r";         
        $str .= "    /**\n\r";
        $str .= "     * @method 客户端断开连接时触发\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type]  \$client_id [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function onClose (\$client_id)\n\r";
        $str .= "    {\n\r";
        $str .= "        if (is_callable(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onClose::init\")) {\n\r";
        $str .= "            call_user_func(\"\\\App\\\Callback\\\\\" . self::\$worker_name . \"\\\onClose::init\", \$client_id);\n\r";
        $str .= "        }\n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        $ok = file_put_contents(callback_path() . DS . "Events.php", $str);
        if (!$ok) {
            throw new Exception("Failed to create file");
        }
    }

    /**
     * @method WorkerMan 的 onWorkerStart 回调文件模版
     *  
     * @param  [type]  $process [description]
     * @return [type]           [description]
     */
    protected static function onWorkerStartWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "use \\support\\bootstrap\\Db;\n\r";
        $str .= "use \\support\\bootstrap\\LoadTimer;\n\r";
        $str .= "\n\r";
        $str .= "class onWorkerStart\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$worker     [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$worker)\n\r";
        $str .= "    {\n\r";
        $str .= "        Db::connect();\n\r";
        $str .= "        LoadTimer::load(\$worker);\n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method WorkerMan 的 onWorkerReload 回调文件模版
     *  
     * @param  [type]  $process [description]
     * @return [type]           [description]
     */
    protected static function onWorkerReloadWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onWorkerReload\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$worker     [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$worker)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method WorkerMan 的 onConnect 回调文件模版
     *  
     * @param  [type]  $process [description]
     * @return [type]           [description]
     */
    protected static function onConnectWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onConnect\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$connection [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$connection)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method WorkerMan 的 onMessage 回调文件模版
     *  
     * @param  [type]  $process [description]
     * @return [type]           [description]
     */
    protected static function onMessageWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onMessage\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$connection [description]\n\r";
        $str .= "     * @param  [type] \$message    [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$connection, \$message)\n\r";
        $str .= "    {\n\r";
        $str .= "        if (\$connection->worker->protocol == \"\\Workerman\\Protocols\\Http\") {\n\r";
        $str .= "            switch (\$message->method()) {\n\r";
        $str .= "                case 'GET':\n\r";
        $str .= "                    \$message_data = \$message->get();\n\r";
        $str .= "                    break;\n\r";
        $str .= "                case 'POST':\n\r";
        $str .= "                    \$message_data = \$message->post();\n\r";
        $str .= "                    break;\n\r";
        $str .= "                default:\n\r";
        $str .= "                    \$connection->send(json(['type'=>'error', 'msg'=>'非法操作！']));\n\r";
        $str .= "                    return;\n\r";
        $str .= "                    break;\n\r";
        $str .= "            }\n\r";
        $str .= "        } else {\n\r";
        $str .= "            if (!is_string(\$message)) {\n\r";
        $str .= "                \$connection->send(json(['type'=>'error', 'msg'=>'非法操作！']));\n\r";
        $str .= "                return;\n\r";
        $str .= "            }\n\r";
        $str .= "            \n\r";
        $str .= "            if (!\$message_data = json_decode(\$message, true)) {\n\r";
        $str .= "                \$connection->send(json(['type'=>'error', 'msg'=>'非法操作！']));\n\r";
        $str .= "                return;\n\r";
        $str .= "            }\n\r";
        $str .= "        }\n\r";
        $str .= "        \n\r";
        $str .= "        if (empty(\$message_data['type'])) {\n\r";
        $str .= "            \$connection->send(json(['type'=>'error', 'msg'=>'非法操作！']));\n\r";
        $str .= "            return;\n\r";
        $str .= "        }\n\r";
        $str .= "        \n\r";
        $str .= "        if (is_callable(\"\\\App\\\Message\\\\" . $process . "\\\\\" . parse_name(\$message_data['type'], 1) . \"::init\")) {\n\r";
        $str .= "            call_user_func(\"\\\App\\\Message\\\\" . $process . "\\\\\" . parse_name(\$message_data['type'], 1) . \"::init\", \$connection, \$message_data);\n\r";
        $str .= "        } else {\n\r";
        $str .= "            \$connection->send(json(['type'=>'error', 'msg'=>'非法操作！']));\n\r";
        $str .= "        }\n\r";

        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method WorkerMan 的 onClose 回调文件模版
     *  
     * @param  [type]  $process [description]
     * @return [type]           [description]
     */
    protected static function onCloseWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onClose\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$connection [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$connection)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method WorkerMan 的 onBufferFull 回调文件模版
     *  
     * @param  [type]  $process [description]
     * @return [type]           [description]
     */
    protected static function onBufferFullWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onBufferFull\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$connection [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$connection)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method WorkerMan 的 onBufferDrain 回调文件模版
     *  
     * @param  [type]  $process [description]
     * @return [type]           [description]
     */
    protected static function onBufferDrainWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onBufferDrain\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$connection [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$connection)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method WorkerMan 的 onError 回调文件模版
     *  
     * @param  [type]  $process [description]
     * @return [type]           [description]
     */
    protected static function onErrorWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onError\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$connection [description]\n\r";
        $str .= "     * @param  [type] \$code       [description]\n\r";
        $str .= "     * @param  [type] \$msg        [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$connection, \$code, \$msg)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method WorkerMan 的 onWorkerStop 回调文件模版
     *  
     * @param  [type]  $process [description]
     * @return [type]           [description]
     */
    protected static function onWorkerStopWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onWorkerStop\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$worker     [description]\n\r";
        $str .= "     * @return [type]             [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$worker)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method GatewayWorker 的 onWorkerStart 回调文件模版
     *  
     * @param  [type]                     $process [description]
     * @return [type]                              [description]
     */
    protected static function onWorkerStartGatewayWorker ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "use \\support\\bootstrap\\Db;\n\r";
        $str .= "use \\support\\bootstrap\\LoadTimer;\n\r";
        $str .= "\n\r";
        $str .= "class onWorkerStart\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$businessWorker [description]\n\r";
        $str .= "     * @return [type]                 [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$businessWorker)\n\r";
        $str .= "    {\n\r";
        $str .= "        Db::connect();\n\r";
        $str .= "        LoadTimer::load(\$businessWorker);\n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method GatewayWorker 的 onConnect 回调文件模版
     *  
     * @param  [type]                     $process [description]
     * @return [type]                              [description]
     */
    protected static function onConnectGatewayWorker ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onConnect\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$client_id   [description]\n\r";
        $str .= "     * @return [type]              [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$client_id)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method GatewayWorker 的 onWebSocketConnect 回调文件模版
     *  
     * @param  [type]                     $process [description]
     * @return [type]                              [description]
     */
    protected static function onWebSocketConnectGatewayWorker ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onWebSocketConnect\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$client_id   [description]\n\r";
        $str .= "     * @param  [type] \$data        [description]\n\r";
        $str .= "     * @return [type]              [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$client_id, \$data)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method GatewayWorker 的 onMessage 回调文件模版
     *  
     * @param  [type]                     $process [description]
     * @return [type]                              [description]
     */
    protected static function onMessageGatewayWorker ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "use \GatewayWorker\Lib\Gateway;\n\r";
        $str .= "\n\r";
        $str .= "class onMessage\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$client_id   [description]\n\r";
        $str .= "     * @param  [type] \$message     [description]\n\r";
        $str .= "     * @return [type]              [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$client_id, \$message)\n\r";
        $str .= "    {\n\r";
        $str .= "        if (!is_string(\$message)) {\n\r";
        $str .= "            Gateway::sendToClient(\$client_id, json(['type'=>'error', 'msg'=>'非法操作！']));\n\r";
        $str .= "            return;\n\r";
        $str .= "        }\n\r";
        $str .= "        \n\r";
        $str .= "        if (!\$message_data = json_decode(\$message, true)) {\n\r";
        $str .= "            Gateway::sendToClient(\$client_id, json(['type'=>'error', 'msg'=>'非法操作！']));\n\r";
        $str .= "            return;\n\r";
        $str .= "        }\n\r";
        $str .= "        \n\r";
        $str .= "        if (empty(\$message_data['type'])) {\n\r";
        $str .= "            Gateway::sendToClient(\$client_id, json(['type'=>'error', 'msg'=>'非法操作！']));\n\r";
        $str .= "            return;\n\r";
        $str .= "        }\n\r";
        $str .= "        \n\r";
        $str .= "        if (is_callable(\"\\\App\\\Message\\\\" . $process . "\\\\\" . parse_name(\$message_data['type'], 1) . \"::init\")) {\n\r";
        $str .= "            call_user_func(\"\\\App\\\Message\\\\" . $process . "\\\\\" . parse_name(\$message_data['type'], 1) . \"::init\", \$client_id, \$message_data);\n\r";
        $str .= "        } else {\n\r";
        $str .= "            Gateway::sendToClient(\$client_id, json(['type'=>'error', 'msg'=>'非法操作！']));\n\r";
        $str .= "        }\n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method GatewayWorker 的 onClose 回调文件模版
     *  
     * @param  [type]                     $process [description]
     * @return [type]                              [description]
     */
    protected static function onCloseGatewayWorker ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onClose\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$client_id   [description]\n\r";
        $str .= "     * @return [type]              [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$client_id)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method GatewayWorker 的 onWorkerStop 回调文件模版
     *  
     * @param  [type]                     $process [description]
     * @return [type]                              [description]
     */
    protected static function onWorkerStopGatewayWorker ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Callback\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class onWorkerStop\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method 初始化\n\r";
        $str .= "     *  \n\r";
        $str .= "     * @param  [type] \$businessWorker [description]\n\r";
        $str .= "     * @return [type]                 [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$businessWorker)\n\r";
        $str .= "    {\n\r";
        $str .= "        \n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method 定时任务模版
     *  
     * @param  [type] $process [description]
     */
    protected static function Timer ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Timer\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class Test\n\r";
        $str .= "{\n\r";
        $str .= "    /**\n\r";
        $str .= "     * 是否运行\n\r";
        $str .= "     * @var boolean\n\r";
        $str .= "     */\n\r";
        $str .= "    public static \$run = false;\n\r";
        $str .= "    \n\r";
        $str .= "    /**\n\r";
        $str .= "     * 间隔时间\n\r";
        $str .= "     * @var integer\n\r";
        $str .= "     */\n\r";
        $str .= "    public static \$interval = 3;\n\r";
        $str .= "    \n\r";
        $str .= "    /**\n\r";
        $str .= "     * 是否是持久的\n\r";
        $str .= "     * @var boolean\n\r";
        $str .= "     */\n\r";
        $str .= "    public static \$persistent = true;\n\r";
        $str .= "    \n\r";
        $str .= "    /**\n\r";
        $str .= "     * @method init\n\r";
        $str .= "     *\n\r";
        $str .= "     * @param  [type] \$timer_id [定时器ID]\n\r";
        $str .= "     * @return [type]           [description]\n\r";
        $str .= "     */\n\r";
        $str .= "    public static function init (\$timer_id)\n\r";
        $str .= "    {\n\r";
        $str .= "        var_dump(\$timer_id);\n\r";
        $str .= "    }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method WorkerMan接收数据处理模版
     *  
     * @param  [type] $process [description]
     */
    protected static function MessageWorkerMan ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Message\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class Test\n\r";
        $str .= "{\n\r";
        $str .= "   /**\n\r";
        $str .= "    * @method Test\n\r";
        $str .= "    *\n\r";
        $str .= "    * @param  [type] \$connection   [description]\n\r";
        $str .= "    * @param  [type] \$message_data [description]\n\r";
        $str .= "    * @return [type]               [description]\n\r";
        $str .= "    */\n\r";
        $str .= "   public static function init (\$connection, \$message_data)\n\r";
        $str .= "   {\n\r";
        $str .= "       \$connection->send(json(['type'=>'test']));\n\r";
        $str .= "   }\n\r";
        $str .= "}\n\r";

        return $str;
    }

    /**
     * @method GatewayWorker接收数据处理模版
     *  
     * @param  [type] $process [description]
     */
    protected static function MessageGatewayWorker ($process)
    {
        $str = "<?php\n\r";
        $str .= "\n\r";
        $str .= "namespace App\Message\\" . $process . ";\n\r";
        $str .= "\n\r";
        $str .= "\n\r";
        $str .= "class Test\n\r";
        $str .= "{\n\r";
        $str .= "   /**\n\r";
        $str .= "    * @method Test\n\r";
        $str .= "    *\n\r";
        $str .= "    * @param  [type] \$client_id    [description]\n\r";
        $str .= "    * @param  [type] \$message_data [description]\n\r";
        $str .= "    * @return [type]               [description]\n\r";
        $str .= "    */\n\r";
        $str .= "   public static function init (\$client_id, \$message_data)\n\r";
        $str .= "   {\n\r";
        $str .= "       Gateway::sendToClient(\$client_id, json(['type'=>'test']));\n\r";
        $str .= "   }\n\r";
        $str .= "}\n\r";

        return $str;
    }
}
