<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/2/24 10:16 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\backend\modules\map\controllers;


use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\Url;

class SettingController extends BaseController
{
    public function index()
    {
        if (request()->input('a_map')) {
            return $this->store();
        }
        return view('map.setting', ['map' => Setting::get('map.a_map')]);
    }

    private function store()
    {
        Setting::set("map.a_map", request()->input('a_map') ?: []);

        return $this->successJson('地图设置成功');
    }

}
