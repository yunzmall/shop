<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/7/9 2:27 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\common\observers\point;


use app\common\observers\BaseObserver;
use app\common\services\finance\PointService;

class BindMobileAwardObserver extends BaseObserver
{
    public function created($model)
    {
        (new PointService([
            'point_mode'        => PointService::POINT_MODE_BIND_MOBILE,
            'member_id'         => $model->member_id,
            'point'             => $model->point,
            'remark'            => '绑定手机奖励积分',
            'point_income_type' => PointService::POINT_INCOME_GET
        ]))->changePoint();
    }
}
