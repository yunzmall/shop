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

class SettingController extends BaseController
{
    public function index()
    {
        if ($this->postData()) return $this->store();

        return view('map.setting', $this->viewData());
    }

    /**
     * 数据存储
     */
    private function store()
    {
        Setting::set("map.a_map", $this->postData());

        return $this->successJson('地图设置成功');
    }

    /**
     * 提交数据
     *
     * @return array
     */
    private function postData()
    {
        return request()->input('a_map', []);
    }

    /**
     * view 数据
     *
     * @return array
     */
    private function viewData()
    {
        return ['map' => Setting::get('map.a_map')];
    }

}
