<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/1/13
 * Time: 16:45
 */

namespace app\frontend\modules\order\operations\member;

use app\frontend\modules\order\operations\OrderOperation;

class CommentAdditional extends OrderOperation
{
    public function getApi()
    {
        return '';
    }

    public function getValue()
    {
        return static::ADDITIONAL_COMMENT;
    }

    public function getName()
    {
        return '追评';
    }

    public function enable()
    {
        //商城关闭退款按钮
        if (!\Setting::get('shop.trade.refund_status')) {
            return false;
        }

        //商品开启不可退款
        if ($this->order->no_refund) {
            return false;
        }
        return $this->order->canRefund();
    }

}