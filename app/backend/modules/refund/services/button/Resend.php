<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/10
 * Time: 17:16
 */

namespace app\backend\modules\refund\services\button;


use app\frontend\modules\refund\models\RefundApply;

class Resend extends RefundButtonBase
{
    public function getApi()
    {
        return 'refund.operation.resend';
    }

    public function getName()
    {
        $name = '确认发货';

        return $name;
    }

    public function getValue()
    {
        return 5;
    }

    public function enable()
    {
        return $this->refund->status < RefundApply::WAIT_RESEND_GOODS;
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