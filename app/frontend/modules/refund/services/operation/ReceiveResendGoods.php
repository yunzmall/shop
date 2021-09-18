<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/3
 * Time: 下午3:43
 */

namespace app\frontend\modules\refund\services\operation;

use app\common\models\Order;
use app\frontend\modules\order\services\OrderService;


class ReceiveResendGoods extends ChangeStatusOperation
{
    protected $statusBeforeChange = [self::WAIT_RECEIVE_RESEND_GOODS];
    protected $statusAfterChanged = self::COMPLETE;
    protected $name = '收货';
    protected $timeField = 'refund_time';

    protected $pastTenseClassName = '';

    protected function updateTable()
    {
        parent::updateTable();
    }

    /**
     * @return bool|void
     * @throws \app\common\exceptions\AppException
     */
    public function execute()
    {
        parent::execute();


        if ($this->order->status == Order::WAIT_SEND) {
            OrderService::orderSend(['order_id' => $this->order_id]);
            OrderService::orderReceive(['order_id' => $this->order_id]);
        } else if ($this->order->status == Order::WAIT_RECEIVE) {
            OrderService::orderReceive(['order_id' => $this->order_id]);
        }

    }
}