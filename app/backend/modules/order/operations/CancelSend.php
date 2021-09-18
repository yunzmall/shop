<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/1
 * Time: 11:45
 */

namespace app\backend\modules\order\operations;


class CancelSend extends BackendOrderBase
{
    public function getApi()
    {
        return 'order.vue-operation.cancel-send';
    }

    public function getName()
    {
        return '取消发货';
    }

    public function getValue()
    {
        return 'cancel_send';
    }

    public function enable()
    {
        return true;
    }

    public function getType()
    {
        return self::TYPE_WARNING;
    }
}