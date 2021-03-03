<?php

namespace support\bootstrap\LogFormatter;

use \Monolog\Formatter\JsonFormatter;

class DebugFormatter extends JsonFormatter
{
    protected $worker;

    public function __construct($worker)
    {
        $this->worker = $worker;
    }

    public function format(array $record): string
    {
        if (!empty($this->worker->eventHandler)) {
            $client_id      = !empty($_SERVER['GATEWAY_CLIENT_ID']) ? $_SERVER['GATEWAY_CLIENT_ID'] : NULL;
            $LocalAddress   = !empty($_SERVER['GATEWAY_CLIENT_ID']) ? $_SERVER['GATEWAY_ADDR'] . ":" . $_SERVER['GATEWAY_PORT'] : NULL;
            $ForeignAddress = !empty($_SERVER['GATEWAY_CLIENT_ID']) ? $_SERVER['REMOTE_ADDR'] . ":" . $_SERVER['REMOTE_PORT'] : NULL;
        } else {
            $connection_id  = $record['context']['connection_id'] ?? NULL;
            $LocalAddress   = !empty($this->worker->connections[$connection_id]) ? $this->worker->connections[$connection_id]->getLocalAddress() : NULL;
            $ForeignAddress = !empty($this->worker->connections[$connection_id]) ? $this->worker->connections[$connection_id]->getRemoteAddress() : NULL;
        }

        $client_id = ($client_id ?? NULL) ?? ($connection_id ?? NULL);

        $datetime   = $record['datetime'];
        $level_name = $record['level_name'];
        $message    = $record['message'];
        $context    = $record['context'] !== [] ? print_r($record['context']['message'] ?? [], true) : "[]";
        $extra      = $record['extra'] !== [] ? print_r($record['extra'], true) : "[]";

        $string = "[ {$datetime} ] [ {$level_name} ] [ {$client_id} ] [ {$LocalAddress} ] [ {$ForeignAddress} ] \n";
        $string .= "[ message ] {$message}\n";
        $string .= "[ context ] {$context}\n";
        $string .= "[ extra ] {$extra}\n";
        $string .= "---------------------------------------------------------------\n";

        return $string;
    }
}
