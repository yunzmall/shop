<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/9
 * Time: 15:16
 */

namespace app\backend\modules\order\steps;



class Receive extends OrderStepFactory
{
    public function getTitle()
    {
        if (!$this->finishStatus()) {
            return '待收货';
        }

        return '完成';
    }

    public function getDescription()
    {
        if ($this->finishStatus()) {
            return $this->order->finish_time->toDateTimeString();
        }
        return parent::getDescription();
    }

    public function isShow()
    {
        return !($this->order->status == -1 &&  $this->order->finish_time->toDateTimeString() == '1970-01-01 08:00:00');
    }

    public function waitStatus()
    {
        return $this->order->status < 2;
    }

    public function processStatus()
    {
        return $this->order->status == 2;
    }

    public function finishStatus()
    {
        return  $this->order->finish_time->toDateTimeString() !='1970-01-01 08:00:00';
    }

    public function sort()
    {
        return 30;
    }
}