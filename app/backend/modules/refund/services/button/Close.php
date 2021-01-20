<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/10
 * Time: 17:17
 */

namespace app\backend\modules\refund\services\button;


class Close extends RefundButtonBase
{
    public function getApi()
    {
        return 'refund.operation.close';
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
        return $this->refund->isRefunding();
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
