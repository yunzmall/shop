<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021/11/24
 * Time: 17:30
 */

namespace app\common\services;


use Illuminate\Support\Facades\Redis;

class RequestTokenService
{

    /**
     * 规定时间内不能再次请求，时间单位：s
     * @param $key
     * @param int $second
     * @return bool false 不可以 true 可以
     */
    public static function limitRepeat($key, $second = 10)
    {

        $code = \YunShop::app()->uniacid."_{$key}";

        $token = Redis::exists($code);

        if ($token) {
            return false;
        }

        Redis::setex($code, $second,$key);


        return true;
    }


    /**
     * @param $key
     * @param int $time
     * @return string
     */
    public static function getRequestToken($key,$time = 120)
    {
        $key .= \YunShop::app()->uniacid;
        $token = Redis::get($key);
        if ($token) {
            return false;   //这个token还在，返回错误
        }
        $token = self::getRandomStr();
        Redis::setex($key , $time, $token);
        return $token;
    }

    public static function checkRequestToken($key,$token)
    {
        $key .= \YunShop::app()->uniacid;
        $hasToken = Redis::get($key);
        if (!$hasToken || $hasToken != $token) {
            return false;
        }
        return true;
    }

    public static function delRequestToken($key)
    {
        $key .= \YunShop::app()->uniacid;
        Redis::del($key);
    }

    /**
     * 获取$length长度的随机字符串
     * @param int $length
     * @return string
     */
    private static function getRandomStr($length = 32)
    {
        $str = '1234567890abcdefghijklmnopqrstuvwxyz';
        $result = '';
        for ($i=1;$i<=$length;$i++) {
            $result .= substr($str,rand(0,(strlen($str)-1)),1);
        }
        return $result.time();
    }
}