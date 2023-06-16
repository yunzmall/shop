<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/27
 * Time: 19:09
 */

namespace app\backend\modules\refund\services\button;


use app\common\models\refund\RefundApply;

class BatchResend  extends RefundButtonBase
{
    public function getApi()
    {
        return 'refund.vue-operation.batch-resend';
    }

    public function getName()
    {

        return '分批发货';
    }

    public function getValue()
    {
        return 'batch_resend';
    }

    public function enable()
    {
        return (RefundApply::WAIT_CHECK < $this->refund->status &&  $this->refund->status < RefundApply::WAIT_RECEIVE_RESEND_GOODS)
            && $this->refund->refundOrderGoods->isNotEmpty();
    }

    public function getType()
    {
        return self::TYPE_PRIMARY;
    }

    public function getDesc()
    {
        if ($this->refund->status < RefundApply::WAIT_RECEIVE_RETURN_GOODS) {
            return '无需客户寄回商品，商家直接发换货商品';
        }

        return '';
    }
}