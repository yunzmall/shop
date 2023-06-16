<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/6
 * Time: 9:24
 */

namespace app\backend\modules\goods\controllers;


use app\common\components\BaseController;

class WidgetInterfaceController extends BaseController
{
    //goods.widget-interface.index
    public function index()
    {

        $data = app('GoodsWidgetContainer')->make('Manager')->handle();
        return $this->successJson('w', $data);
    }

    /**
     * @return mixed
     */
    public function getWidgetColumn()
    {
        $data = app('GoodsWidgetContainer')->make('Manager')->handle();
        return $data;
    }
}