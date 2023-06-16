<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/11/3
 * Time: 15:58
 */

namespace app\common\modules\refund\product;


class ShopRefundOrder extends RefundOrderTypeBase
{
    public function isBelongTo()
    {
        return true;
    }

    public function multipleRefund()
    {
        return $this->order->plugin_id == 0;
    }

    /**
     *
     * @return bool|int 返回false不限制
     */
    public function applyNumberLimit()
    {
        return false;
    }



    public function applyBeforeValidate()
    {

    }
}