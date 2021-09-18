<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/7/13
 * Time: 15:35
 */

namespace app\frontend\modules\order\operations\member;

use app\common\models\Order;
use app\frontend\modules\order\operations\OrderOperation;

class ExpeditingDelivery extends OrderOperation
{

    public $expediting_delivery;
    public $expediting_set;
    public $order;

    public function __construct(Order $order)
    {
        parent::__construct($order);
        if ($this->getExpediting()) {
           $this->expediting_delivery = 1;
        } else {
            $this->expediting_delivery = 2;
        }

        $this->order = $order;

        $this->expediting_set = \Setting::get('shop.order');
    }

    public function enable()
    {
        // TODO: Implement enable() method.
        if ($this->expediting_set['expediting_delivery'] == 1 && in_array($this->order->plugin_id,[0,92,32])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        // TODO: Implement getName() method.
        if ($this->expediting_delivery == 1) {
            return '已催发货';
        }

        return '催发货';
    }

    /**
     * @return string
     */
    public function getValue()
    {
        // TODO: Implement getValue() method.
        return 'expediting_delivery';
    }

    /**
     * @return string
     */
    public function getApi()
    {
        // TODO: Implement getApi() method.
        if ($this->expediting_delivery == 1) {
            return '';
        }

        return 'order.order-expediting-delivery.index';
    }

    private function getExpediting()
    {
        $order = \app\common\models\ExpeditingDelivery::where("order_id",$this->order->id)->first();

        if ($order) {
            return true;
        }

        return false;
    }
}