<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/9
 * Time: 15:12
 */

namespace app\backend\modules\order\steps;


class Create extends OrderStepFactory
{
    public function getTitle()
    {
        return '下单时间';
    }

    public function getDescription()
    {
        if ($this->finishStatus()) {
            return $this->order->create_time->toDateTimeString();
        }
        return parent::getDescription();
    }


    public function waitStatus()
    {
       return false;
    }

    public function processStatus()
    {
        return false;
    }

    public function finishStatus()
    {
       return true;
    }


    public function sort()
    {
        return 0;
    }


}