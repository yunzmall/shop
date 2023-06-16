<?php
/**
 * Created by PhpStorm.
 * User: liuyifan
 * Date: 2019/2/27
 * Time: 10:53
 */

namespace app\platform\modules\system\controllers;

use app\platform\controllers\BaseController;
use app\platform\modules\system\models\SystemSetting;

class SiteController extends BaseController
{
    public function index()
    {
        $set_data = request()->setdata;
        $copyright = SystemSetting::settingLoad('copyright', 'system_copyright');
        if ($set_data) {
            $site = SystemSetting::settingSave($set_data, 'copyright', 'system_copyright');
            if ($set_data['title_icon']) {
                $title_icon = file_get_contents($set_data['title_icon']);
                file_put_contents(base_path().'/favicon.ico', $title_icon);
            }
            if ($site) {
                return $this->successJson('成功', '');
            } else {
                return $this->errorJson('失败', '');
            }
        }
        if ($copyright) {
            $copyright['site_logo'] = yz_tomedia($copyright['site_logo']);
            $copyright['title_icon'] = yz_tomedia($copyright['title_icon']);
            $copyright['advertisement'] = yz_tomedia($copyright['advertisement']);
            return $this->successJson('成功', $copyright);
        } else {
            return $this->errorJson('没有检测到数据', '');
        }
    }
}