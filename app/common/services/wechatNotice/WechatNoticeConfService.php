<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
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