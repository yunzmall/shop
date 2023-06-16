<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/13
 * Time: 17:13
 */

namespace app\frontend\modules\refund\services\back_way_operation;


class SelfSend extends RefundBackWayOperation
{
    public $code = 'self_send';
    public $value = 0;
    public $name = '自行寄回';

    public function init()
    {

    }

    public function saveRelation()
    {

    }

    public function getEditData()
    {
        return [];
    }

    public function getOtherData()
    {
        return [];
    }

    public function isEnabled()
    {
        return true;
    }
}