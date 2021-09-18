<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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