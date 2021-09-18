<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
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
}