<?php

namespace app\backend\modules\siteSetting\controllers;

use app\common\components\BaseController;
use app\common\exceptions\AppException;
use app\common\facades\SiteSetting;
use app\common\helpers\Url;

class StoreController extends BaseController
{
    public function index()
    {
        $setting = request()->input('setting');

        if (preg_match("/^(http:\/\/).*$/", $setting['host']) || preg_match("/^(https:\/\/).*$/", $setting['host'])) {
            $this->errorJson('无需填写’HTTP‘或者’HTTPS‘');
        }

//        if (substr($setting['host'],-1) == '/' || substr($setting['host'],-1) == "\\"){
//            $setting['host'] = substr($setting['host'], 0, -1);
//        }

        //过滤字符/
        $url = rtrim($setting['host'], '/');
        $setting["host"] = $url;

        SiteSetting::set('base', $setting);

        return $this->successJson("设置保存成功", Url::absoluteWeb('siteSetting.index.index'));
    }

    public function queue()
    {
        $setting = request()->input('setting');
        SiteSetting::set('queue', $setting);

        return $this->successJson("设置保存成功", Url::absoluteWeb('siteSetting.queue.index'));
    }

    public function websocket()
    {
        $setting = request()->input('setting');
        SiteSetting::set('websocket', $setting);

        return $this->successJson("设置保存成功");
    }
}