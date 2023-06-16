<?php
/**
 * Author:
 * Date: 2017/8/21
 * Time: 下午5:36
 */

namespace app\common\events\finance;


use app\common\events\Event;

class AlipayWithdrawEvent extends Event
{
    protected $trade_no;


    public function __construct($trade_no)
    {
        $this->trade_no = $trade_no;
    }

    public function getTradeNo()
    {
        return $this->trade_no;
    }
}