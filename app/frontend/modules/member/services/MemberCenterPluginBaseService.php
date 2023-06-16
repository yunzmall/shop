<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/1
 * Time: 下午4:37
 */

namespace app\frontend\modules\member\services;

use app\common\helpers\Cache;

abstract class MemberCenterPluginBaseService
{
    public $request;

    public function init($request)
    {
        $this->request = $request;
    }

    abstract function getEnabled();

    abstract function getData();

    public function setRedis($key,$value,$expire = 5)
    {
        Cache::put($key,$value ,$expire);
    }

    public function getRedis($key)
    {
        if (!Cache::has($key)) {
            return null;
        }
        return Cache::get($key);
    }

    public function getShowVipPrice()
    {
        $set = \Setting::get('plugin.member-price.is_open_micro');
        if (!app('plugins')->isEnabled('member-price') || $set != 1) {
            return true;
        }
        return false;
    }
}