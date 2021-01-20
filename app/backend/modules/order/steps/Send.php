<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/9
 * Time: 15:15
 */

namespace app\backend\modules\order\steps;



class Send extends OrderStepFactory
{
    public function getTitle()
    {
        if (!$this->finishStatus()) {
            return '待发货';
        }
        return '发货时间';
    }

    public function getDescription()
    {
        if ($this->finishStatus()) {
            return $this->order->send_time->toDateTimeString();
        }
        return parent::getDescription();
    }

    public function isShow()
    {
        return !($this->order->status == -1 && $this->order->send_time->toDateTimeString() == '1970-01-01 08:00:00');
    }

    public function waitStatus()
    {
        return $this->order->status < 1;
    }

    public function processStatus()
    {
        return $this->order->status == 1;
    }

    public function finishStatus()
    {
        return $this->order->send_time->toDateTimeString() != '1970-01-01 08:00:00';

    }

    public function sort()
    {
        return 20;
    }
}