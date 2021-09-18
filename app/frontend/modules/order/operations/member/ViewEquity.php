<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/8/3
 * Time: 下午5:06
 */

namespace app\frontend\modules\order\operations\member;

use app\frontend\modules\order\operations\OrderOperation;

class ViewEquity extends OrderOperation
{
    public function getApi()
    {
        return 'plugin.aggregation-cps.api.equity.order-detail';
    }

    public function getName()
    {
        return '查看卡券';
    }

    public function getValue()
    {
        return 51;
    }

    public function enable()
    {
        if ($this->order->plugin_id == 71) {
            return true;
        }
        return false;
    }
}