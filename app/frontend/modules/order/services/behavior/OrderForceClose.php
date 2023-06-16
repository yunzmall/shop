<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/28
 * Time: 上午11:07
 * comment:订单关闭类
 */

namespace app\frontend\modules\order\services\behavior;
use app\common\events\order\AfterOrderCanceledEvent;
use app\common\events\order\BeforeOrderCloseEvent;
use app\common\models\Order;


class OrderForceClose extends ChangeStatusOperation
{
    protected $statusBeforeChange = [ORDER::WAIT_PAY,ORDER::WAIT_SEND,Order::WAIT_RECEIVE,Order::COMPLETE];
    protected $statusAfterChanged = ORDER::CLOSE;
    protected $name = '关闭';
    protected $time_field = 'cancel_time';
    protected $past_tense_class_name = 'OrderCanceled';

    public $params = [];
    /**
     * @return \app\common\events\order\CreatedOrderEvent
     */
    protected function getBeforeEvent()
    {
        return new BeforeOrderCloseEvent($this);
    }

    /**
     * @return bool|void
     */
    protected function updateTable()
    {
        $data = $this->params ? $this->params : request()->input();


        if (!empty($data['reson'])) {
            $this->close_reason = $data['reson'];
        }
        parent::updateTable();
    }
}