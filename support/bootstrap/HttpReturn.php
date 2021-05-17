<?php

namespace support\bootstrap;

use Workerman\Protocols\Http\Response;

/**
 * Workerman Http协议数据输出
 *
 * @Author    HSK
 * @DateTime  2021-05-17 22:58:51
 */
class HttpReturn
{
    /**
     * 返回任意响应
     *
     * @Author    HSK
     * @DateTime  2021-05-17 23:06:14
     *
     * @param string $body
     * @param integer $status
     * @param array $headers
     *
     * @return object
     */
    public static function response(string $body = '', int $status = 200, array $headers = array()): object
    {
        return new Response($status, $headers, $body);
    }

    /**
     * 返回JSON
     *
     * @Author    HSK
     * @DateTime  2021-05-17 23:05:51
     *
     * @param mixed $data
     * @param [type] $options
     *
     * @return object
     */
    public static function json(mixed $data, $options = JSON_UNESCAPED_UNICODE): object
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
    }

    /**
     * 返回XML
     *
     * @Author    HSK
     * @DateTime  2021-05-17 23:04:06
     *
     * @param string $xml
     *
     * @return object
     */
    public static function xml(string $xml): object
    {
        if ($xml instanceof \SimpleXMLElement) {
            $xml = $xml->asXML();
        }
        return new Response(200, ['Content-Type' => 'text/xml'], $xml);
    }

    /**
     * 返回JSONP
     *
     * @Author    HSK
     * @DateTime  2021-05-17 23:00:25
     *
     * @param mixed $data
     * @param string $callback_name
     *
     * @return object
     */
    public static function jsonp(mixed $data, $callback_name = 'callback'): object
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
     * @DateTime  2021-05-17 23:00:01
     *
     * @param string $location
     * @param integer $status
     * @param array $headers
     *
     * @return object
     */
    public static function redirect(string $location, int $status = 302, array $headers = []): object
    {
        $response = new Response($status, ['Location' => $location]);
        if (!empty($headers)) {
            $response->withHeaders($headers);
        }
        return $response;
    }
}
