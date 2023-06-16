<?php
/**
 * Created by PhpStorm.
 * User: CJVV
 * Date: 2021/3/17
 * Time: 16:36
 */

namespace app\common\events\order;

use app\common\events\Event;
use app\common\models\refund\RefundApply;

/**
 * 退款申请审核通过
 * Class AfterOrderRefundCancelEvent
 * @package app\common\events\order
 */
class AfterOrderAddressChangeEvent extends Event
{
    /**
     * @var RefundApply
     */
    protected $address_log;

    public function __construct($address_log)
    {
        $this->address_log = $address_log;
    }

    /**
     * @return RefundApply
     */
    public function getModel()
    {
        return $this->address_log;
    }

}