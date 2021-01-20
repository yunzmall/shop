<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/10
 * Time: 17:15
 */

namespace app\backend\modules\refund\services\button;


class Pass extends RefundButtonBase
{
    public function getApi()
    {
        return 'refund.operation.pass';
    }

    public function getName()
    {
        return '通过申请';
    }

    public function getValue()
    {
        return 3;
    }

    public function enable()
    {
        return $this->refund->status == 0;
    }

    public function getType()
    {
        return self::TYPE_SUCCESS;
    }

    public function getDesc()
    {
       return '需客户寄回商品';
    }
}