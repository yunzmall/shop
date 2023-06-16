<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/10
 * Time: 17:17
 */

namespace app\backend\modules\refund\services\button;


use app\common\models\refund\RefundApply;

class Close extends RefundButtonBase
{
    public function getApi()
    {
        return 'refund.vue-operation.close';
    }

    public function getName()
    {
        return '关闭申请';
    }

    public function getValue()
    {
        return 10;
    }

    public function enable()
    {
        return $this->refund->isRefunding() && RefundApply::WAIT_CHECK < $this->refund->status;
    }

    public function getType()
    {
        return self::TYPE_WARNING;
    }

    public function getDesc()
    {
        return '换货完成';
    }
}
