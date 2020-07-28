<?php

namespace support\bootstrap;

use Workerman\Protocols\Http\Response;

class HttpReturn
{
    /**
     * @method 返回任意响应
     *
     * @param  string   $body    [description]
     * @param  integer  $status  [description]
     * @param  array    $headers [description]
     * @return [type]            [description]
     */
    public static function response($body = '', $status = 200, $headers = array())
    {
        return new Response($status, $headers, $body);
    }

    /**
     * @method 返回JSON
     *
     * @param  [type] $data    [description]
     * @param  [type] $options [description]
     * @return [type]          [description]
     */
    public static function json($data, $options = JSON_UNESCAPED_UNICODE)
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
    }

    /**
     * @method 返回XML
     *
     * @param  [type] $xml [description]
     * @return [type]      [description]
     */
    public static function xml($xml)
    {
        if ($xml instanceof SimpleXMLElement) {
            $xml = $xml->asXML();
        }
        return new Response(200, ['Content-Type' => 'text/xml'], $xml);
    }

    /**
     * @method 返回JSONP
     *
     * @param  [type] $data          [description]
     * @param  string $callback_name [description]
     * @return [type]                [description]
     */
    public static function jsonp($data, $callback_name = 'callback')
    {
        if (!is_scalar($data) && null !== $data) {
            $data = json_encode($data);
        }
        return new Response(200, [], "$callback_name($data)");
    }

    /**
     * @method 重定向
     *
     * @param  [type]   $location [description]
     * @param  integer  $status   [description]
     * @param  array    $headers  [description]
     * @return [type]             [description]
     */
    public static function redirect($location, $status = 302, $headers = [])
    {
        $response = new Response($status, ['Location' => $location]);
        if (!empty($headers)) {
            $response->withHeaders($headers);
        }
        return $response;
    }
}
