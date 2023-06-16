<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2021/2/2
 * Time: 11:49
 */
namespace app\common\modules\sms\factory;


use app\platform\modules\system\models\SystemSetting;

class SmsFactory
{
    /**
     * 互亿无线
     */
    const SMS_HUYI = 1;

    /**
     * 阿里大鱼
     */
    const SMS_ALI_DAYU = 2;

    /**
     * 阿里云
     */
    const SMS_ALI_YUN = 3;

    /**
     * 腾讯云，独立框架
     */
    const SMS_TX_YUN_PLATFORM = 4;
    /**
     * 腾讯云
     */
    const SMS_TX_YUN = 5;
    /**
     * elisoftsms
     */
    const SMS_ELISOFTSMS = 6;
    /**
     * 乐信
     */
    const SMS_LX = 7;

    public static function getSmsFactory($type = 0)
    {
        $className = NULL;

        $sms = \Setting::get('shop.sms');

        if($type){
            $sms = SystemSetting::settingLoad('sms', 'system_sms');
        }

        switch ($sms['type']) {

            case self::SMS_HUYI:
                $className = new HuyiSms($sms);
                break;

            case self::SMS_ALI_DAYU:
                $className = new AliDayuSms($sms);
                break;

            case self::SMS_ALI_YUN:
                $className = new AliYunSms($sms);
                break;

            case self::SMS_TX_YUN_PLATFORM:
                $className = new TxYunSms($sms);
                break;

            case self::SMS_TX_YUN:
                $className = new TxYunSms($sms);
                break;

            case self::SMS_ELISOFTSMS:
                $className = new ElisoftSms($sms);
                break;

            case self::SMS_LX:
                $className = new LxSms($sms);
                break;
        }

        return $className;
    }


}