<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/9
 * Time: 16:40
 */

namespace app\backend\modules\order\steps;


class Cancel extends OrderStepFactory
{
    public function getTitle()
    {
        return '关闭时间';
    }

    public function isShow()
    {
       return $this->order->status == -1;
    }


    public function getDescription()
    {
        return $this->order->cancel_time->toDateTimeString();
    }

    public function getStatus()
    {
        return 'error';
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
        return false;
    }


    public function sort()
    {
        return 99999;
    }
}