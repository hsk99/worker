<?php

namespace support\extend;

use Firebase\JWT\JWT;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;
use DateTime;

class Token
{

    /**
     * @method 生成Token
     *
     * @param  array   $data [description]
     * @param  integer $exp  [description]
     * @param  integer $nbf  [description]
     * @param  string  $aud  [description]
     * @return [type]        [description]
     */
    public static function encode ($data = [], $exp = 0, $nbf = 0, $aud = "")
    {
        $config = config('token');
        $time   = time();

        if (empty($exp)) $exp = $config['exp'];
        if (empty($nbf)) $nbf = $config['nbf'];
        if (empty($aud)) $aud = $config['aud'];

        $payload = [
            'iss'  => $config['iss'],// 签发者
            'aud'  => $aud,// 接收者
            'iat'  => $time,// 签发时间
            'nbf'  => $time + $nbf,// 生效时间，某个时间点后才能访问
            'exp'  => $time + $exp,// 过期时间
            'data' => $data // 自定义信息
        ];

        try {
            $token = JWT::encode($payload,$config['key']);
            return ['code'=>200, 'token'=>$token, 'exp' => $payload['exp']];
        } catch(InvalidArgumentException $e) {
            return ['code'=>400, 'msg'=>$e->getMessage()];
        } catch(UnexpectedValueException $e) {
            return ['code'=>400, 'msg'=>$e->getMessage()];
        } catch(DomainException $e) {
            return ['code'=>400, 'msg'=>$e->getMessage()];
        }
    }

    /**
     * @method 解析Token
     *
     * @param  string $token [description]
     * @return [type]        [description]
     */
    public static function decode ($token = "")
    {
        if (empty($token)) {
            return ['code'=>400, 'msg'=>'Token不能为空'];
        }

        $config = config('token');

        try {
            JWT::$leeway = $config['leeway'];//当前时间减去60，把时间留点余地
            $decoded = JWT::decode($token, $config['key'], ['HS256']); //HS256方式，这里要和签发的时候对应
            $decoded = json_decode(json_encode($decoded), true);
            return ['code'=>200, 'data'=>$decoded];
        } catch(\Firebase\JWT\SignatureInvalidException $e) {  //签名不正确
            return ['code'=>400, 'msg'=>$e->getMessage()];
        } catch(\Firebase\JWT\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            return ['code'=>400, 'msg'=>$e->getMessage()];
        } catch(\Firebase\JWT\ExpiredException $e) {  // token过期
            return ['code'=>400, 'msg'=>$e->getMessage()];
        } catch(InvalidArgumentException $e) {
            return ['code'=>400, 'msg'=>$e->getMessage()];
        } catch(UnexpectedValueException $e) {
            return ['code'=>400, 'msg'=>$e->getMessage()];
        } catch(DomainException $e) {
            return ['code'=>400, 'msg'=>$e->getMessage()];
        }
    }
}