<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/10
 * Time: 17:09
 */

namespace app\backend\modules\refund\services\button;


class Pay extends RefundButtonBase
{
    public function getApi()
    {
        return 'refund.pay';
    }

    public function getName()
    {
        return '同意退款';
    }

    public function getValue()
    {
        return 1;
    }

    public function enable()
    {
        return $this->refund->isRefunding();
    }

    public function getType()
    {
        return self::TYPE_SUCCESS;
    }

    public function getDesc()
    {
        if ($this->refund->status == \app\common\models\refund\RefundApply::WAIT_CHECK) {
            $array[] = '无需客户发货直接退款';

        }

        if ($this->refund->status == \app\common\models\refund\RefundApply::WAIT_RECEIVE_RETURN_GOODS) {
            $array[] =  '您已经收到客户寄出的快递';
        }

        $array[] = '该订单付款的方式如有对接退款方式则原路返回，无则需要您用其他方式进行退款';

        return $array;
    }
}