<?php

namespace support\bootstrap;

use Workerman\Protocols\Http\Response;

/**
 * Workerman Http协议数据输出
 *
 * @Author    HSK
 * @DateTime  2020-10-22 17:22:33
 */
class HttpReturn
{
    /**
     * 返回任意响应
     *
     * @Author    HSK
     * @DateTime  2020-10-22 17:23:10
     *
     * @param string $body
     * @param int $status
     * @param array $headers
     *
     * @return void
     */
    public static function response($body = '', $status = 200, $headers = array())
    {
        return new Response($status, $headers, $body);
    }

    /**
     * 返回JSON
     *
     * @Author    HSK
     * @DateTime  2020-10-22 17:23:20
     *
     * @param [type] $data
     * @param [type] $options
     *
     * @return void
     */
    public static function json($data, $options = JSON_UNESCAPED_UNICODE)
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
    }

    /**
     * 返回XML
     *
     * @Author    HSK
     * @DateTime  2020-10-22 17:23:27
     *
     * @param [type] $xml
     *
     * @return void
     */
    public static function xml($xml)
    {
        if ($xml instanceof SimpleXMLElement) {
            $xml = $xml->asXML();
        }
        return new Response(200, ['Content-Type' => 'text/xml'], $xml);
    }

    /**
     * 返回JSONP
     *
     * @Author    HSK
     * @DateTime  2020-10-22 17:23:33
     *
     * @param [type] $data
     * @param string $callback_name
     *
     * @return void
     */
    public static function jsonp($data, $callback_name = 'callback')
    {
        if (!is_scalar($data) && null !== $data) {
            $data = json_encode($data);
        }
        return new Response(200, [], "$callback_name($data)");
    }

    /**
     * 重定向
     *
     * @Author    HSK
     * @DateTime  2020-10-22 17:23:39
     *
     * @param [type] $location
     * @param int $status
     * @param array $headers
     *
     * @return void
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
