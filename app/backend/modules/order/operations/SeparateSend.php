<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/21
 * Time: 9:51
 */

namespace app\backend\modules\order\operations;


class SeparateSend extends BackendOrderBase
{
    public function getApi()
    {
        return 'order.vue-operation.separate-send';
    }

    public function getName()
    {
        if ($this->order->status == 1) {
            return '多包裹发货';
        }
        return '继续发货';
    }

    public function getValue()
    {
        return 'separate_send';
    }

    public function enable()
    {

        //只有标准订单支持多包裹发货
        if ($this->order->plugin_id != 0) {
            return false;
        }

        //已发完货
        if ($this->order->status == 2 && $this->order->is_all_send_goods != 1) {
            return false;
        }

        return true;
    }

    public function getType()
    {
        return self::TYPE_PRIMARY;
    }
}