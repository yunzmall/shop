<?php
/**
 * Author:
 * Date: 2017/6/19
 * Time: 下午12:00
 */

namespace app\common\events\payment;


use app\common\events\Event;

class RechargeComplatedEvent extends Event
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getRechargeData()
    {
        return $this->data;
    }
}