<?php

namespace support\extend;

use Firebase\JWT\JWT;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;
use DateTime;

/**
 * Web Token
 *
 * @Author    HSK
 * @DateTime  2021-05-17 22:30:28
 */
class Token
{
    /**
     * 生成
     *
     * @Author    HSK
     * @DateTime  2021-05-17 22:32:59
     *
     * @param array $data
     * @param integer $exp
     * @param integer $nbf
     * @param string $aud
     *
     * @return array
     */
    public static function encode(array $data = [], int $exp = 0, int $nbf = 0, string $aud = ''): array
    {
        $config = config('token');
        $time   = time();

        if (empty($exp)) $exp = (int)$config['exp'];
        if (empty($nbf)) $nbf = (int)$config['nbf'];
        if (empty($aud)) $aud = (string)$config['aud'];

        $payload = [
            'iss'  => $config['iss'],   // 签发者
            'aud'  => $aud,             // 接收者
            'iat'  => $time,            // 签发时间
            'nbf'  => $time + $nbf,     // 生效时间，某个时间点后才能访问
            'exp'  => $time + $exp,     // 过期时间
            'data' => $data             // 自定义信息
        ];

        try {
            $token = JWT::encode($payload, $config['key']);

            return ['code' => 200, 'token' => $token, 'exp' => $payload['exp']];
        } catch (InvalidArgumentException $e) {
            return ['code' => 400, 'msg' => $e->getMessage()];
        } catch (UnexpectedValueException $e) {
            return ['code' => 400, 'msg' => $e->getMessage()];
        } catch (DomainException $e) {
            return ['code' => 400, 'msg' => $e->getMessage()];
        }
    }

    /**
     * 解析
     *
     * @Author    HSK
     * @DateTime  2021-05-17 22:36:08
     *
     * @param string $token
     *
     * @return array
     */
    public static function decode(string $token): array
    {
        if (empty($token)) {
            return ['code' => 400, 'msg' => 'Token不能为空'];
        }

        $config = config('token');

        try {
            JWT::$leeway  = (int)$config['leeway'];

            $decoded = JWT::decode($token, $config['key'], ['HS256']);
            $decoded = json_decode(json_encode($decoded), true);

            return ['code' => 200, 'data' => $decoded];
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return ['code' => 400, 'msg' => $e->getMessage()];
        } catch (\Firebase\JWT\BeforeValidException $e) {
            return ['code' => 400, 'msg' => $e->getMessage()];
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ['code' => 400, 'msg' => $e->getMessage()];
        } catch (InvalidArgumentException $e) {
            return ['code' => 400, 'msg' => $e->getMessage()];
        } catch (UnexpectedValueException $e) {
            return ['code' => 400, 'msg' => $e->getMessage()];
        } catch (DomainException $e) {
            return ['code' => 400, 'msg' => $e->getMessage()];
        }
    }
}
