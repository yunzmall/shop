<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/12/28
 * Time: 11:43
 */

namespace app\backend\modules\goods\controllers;

use app\common\components\BaseController;
use app\backend\modules\goods\models\GoodsSetting;
use app\common\facades\Setting;

class GoodsSettingController extends BaseController
{
    public function index()
    {
        $data = request()->data;
        $config_data = GoodsSetting::getSet();
        if ($data) {
            if ($config_data) {
                GoodsSetting::find($config_data['id'])->delete();
            }

            GoodsSetting::saveSet($data);
            Setting::set('goods.profit_show_status', $data['profit_show_status'] ? 1 : 0);
            Setting::set('goods.hide_goods_sales', $data['hide_goods_sales'] ? 1 : 0);
            return $this->successJson('设置保存成功');
        }
        $config_data['profit_show_status'] = Setting::get('goods.profit_show_status') ? 1 : 0;
        $config_data['hide_goods_sales'] = Setting::get('goods.hide_goods_sales') ? 1 : 0;
        return view('goods.setting.index', [
            'set' => json_encode($config_data)
        ])->render();

    }
}