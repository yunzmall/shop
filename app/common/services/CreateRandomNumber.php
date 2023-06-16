<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/7/2
 * Time: 11:51
 */

namespace app\common\services;


use Illuminate\Support\Facades\Redis;

class CreateRandomNumber
{
    /**
     * @param $prefix
     * @return string
     */
    public static function sn($prefix)
    {
        return self::redisCache($prefix);

        $unique = substr(uniqid(), 7, 13);

        return $prefix . date('ymdHi') .strtoupper($unique);
    }

    public static function redisCache($prefix)
    {
        $redis =  new Redis();

        $sn = CreateRandomNumber::randomNumber($prefix);

        //没有60秒redis缓存直接返回订单号
        if (!$redis::exists('random_sn')) {
            $redis::sAdd('random_sn', $sn);
            $redis::expire('random_sn', 60);

            return $sn;
        }

        while (1) {
            //集合中是否存在该值
            $bool = $redis::sIsMember('random_sn', $sn);
            if (!$bool) {
                $redis::sAdd('random_sn', $sn);
                break;
            }
            $sn = CreateRandomNumber::randomNumber($prefix);
        }

        return $sn;
    }

    public static function randomNumber($prefix)
    {
        $unique = substr(uniqid(), 7, 13);

        return $prefix . date('ymdHi') .strtoupper($unique);
    }

}