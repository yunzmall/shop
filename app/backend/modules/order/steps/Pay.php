<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/9
 * Time: 15:14
 */

namespace app\backend\modules\order\steps;



class Pay extends OrderStepFactory
{
    public function getTitle()
    {
        if (!$this->finishStatus()) {
            return '待支付';
        }

        return '支付时间';
    }

    public function getDescription()
    {
        if ($this->finishStatus()) {
            return $this->order->pay_time->toDateTimeString();
        }
        return parent::getDescription();
    }

    public function getStatus()
    {
        if ($this->finishStatus()) {
            return 'finish';
        } elseif ($this->processStatus()) {
            return 'process';
        } elseif ($this->waitStatus()) {
            return 'wait';
        }

        return 'error';
    }

    public function isShow()
    {
        return !($this->order->status == -1 &&  $this->order->pay_time->toDateTimeString() == '1970-01-01 08:00:00');
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
        return  $this->order->pay_time->toDateTimeString() !='1970-01-01 08:00:00';
    }

    public function sort()
    {
        return 10;
    }
}