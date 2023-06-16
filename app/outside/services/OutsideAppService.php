<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022/1/6
 * Time: 11:36
 */

namespace app\outside\services;


class OutsideAppService
{

    /**
     * 年月日 + 三位公众号 + 5位随机数
     * @return string
     */
    public static function createAppId()
    {

        $currentUniacid = \YunShop::app()->uniacid;
        $unique  =  hexdec(uniqid());
        $random  = substr($unique, -5, 5);
        $uniacid = str_pad($currentUniacid,3,0,STR_PAD_LEFT);
        $appId = date("Ymd"). $uniacid.$random;

        return $appId;
    }

    public static function createSecret($appId = '')
    {
        if (empty($appId)) {
            $appId = self::createAppId();
        }

        return strtoupper(md5($appId.str_random()));
    }

    public function jiamihash($data, $appSecret, $isBinary = false)
    {
        $sign = hash_hmac('sha256', $data, $appSecret,$isBinary);

        return $sign;
    }


    /**
     * 将参数转换成k=v拼接的形式
     * @param $parameter
     * @return string
     */
    public function toQueryString($parameter)
    {

        //按key的字典序升序排序，并保留key值
        ksort($parameter);

        $strQuery="";
        foreach ($parameter as $k=>$v){

            //不参与签名、验签
            if($k == "sign"){
                continue;
            }

            if($v === null) {$v = '';}

            $strQuery .= strlen($strQuery) == 0 ? "" : "&";
            $strQuery.=$k."=".$v;
        }

        return $strQuery;
    }
}