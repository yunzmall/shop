<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/10
 * Time: 17:14
 */

namespace app\backend\modules\refund\services\button;


class Consensus extends RefundButtonBase
{
    public function getApi()
    {
        return 'refund.vue-operation.consensus';
    }

    public function getName()
    {
        return '手动退款';
    }

    public function getValue()
    {
        return 2;
    }

    public function enable()
    {
        return $this->refund->isRefunding();
    }

    public function getType()
    {
        return self::TYPE_PRIMARY;
    }

    public function getDesc()
    {
        return '您用其他方式进行退款，或者退款金额为0时';
    }
}