<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/4/28
 * Time: 15:09
 */

namespace app\common\services\wechatNotice;


use Yunshop\WechatNotice\services\ConfigService;

class WechatNoticeConfService
{
    protected static $isEnabled;

    protected static function validator()
    {
        if (!isset(self::$isEnabled) && !is_bool(self::$isEnabled)) {
            if (app('plugins')->isEnabled('wechat-notice')) {
                self::$isEnabled = true;
            } else {
                self::$isEnabled = false;
            }
        }
        return self::$isEnabled;
    }

    protected static function config()
    {
        return ConfigService::current();
    }

    public static function getItems()
    {
        if (!self::validator()) {
            return [];
        }
        return self::config()->_getItem();
    }

    public static function getItem($key)
    {
        return array_get(self::getItems(), $key);
    }

    public static function set($key, $value = null)
    {
        if (!self::validator()) {
            return;
        }
        self::config()->set($key, $value);
    }

    public static function push($key, $value)
    {
        if (!self::validator()) {
            return;
        }
        self::config()->push($key, $value);
    }

    public static function batchPush($key, $value)
    {
        if (!self::validator()) {
            return;
        }
        self::config()->batchPush($key, $value);
    }

    public static function unshift($key, $value)
    {
        if (!self::validator()) {
            return;
        }
        self::config()->unshift($key, $value);
    }
}