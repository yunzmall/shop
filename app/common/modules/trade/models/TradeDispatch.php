<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/23
 * Time: 5:11 PM
 */

namespace app\common\modules\trade\models;

use app\common\models\BaseModel;
use app\frontend\modules\order\dispatch\DispatchTypeMenu;
use app\frontend\modules\order\models\PreOrder;


class TradeDispatch extends BaseModel
{
    protected $appends = ['delivery_method'];

    /**
     * @var Trade
     */
    private $trade;

    public function init(Trade $trade)
    {
        $this->trade = $trade;
        $this->setRelation('default_member_address', $this->getMemberAddress());
        return $this;
    }

    /**
     * @return mixed
     */
    private function getMemberAddress()
    {
        return $this->trade->orders->first()->orderAddress->getMemberAddress();
    }

    /**
     * 交易中所有商品配送方式的交集
     * @return array
     */
    protected function _gteDeliveryMethod()
    {

        //todo 虚拟订单不需要配送方式
        $need_address = $this->trade->orders->contains(function (PreOrder $order) {
            return $order->isVirtual() === false;
        });

        if (!$need_address) {
            return [];
        }

        $orders = $this->trade->orders;

        // 遍历获取订单的有效配送方式
        foreach ($this->trade->orders as $order) {
        $dispatchTypeManager = new \app\frontend\modules\order\dispatch\DispatchTypeMenuManager($order, $orders);
            $dispatchTypes = $dispatchTypeManager->getOrderDispatchType();
            $parameter = $dispatchTypes->map(function (DispatchTypeMenu $dispatchType) {
                return [
                    'dispatch_type_id' => $dispatchType->getId(),
                    'name' => $dispatchType->getName(),
                ];
            })->values();
            if ($parameter->isNotEmpty()) {
                $parameters[] = $parameter;
            }
        }
        if (empty($parameters)) {
            return [];
        }

        $result = $parameters[0];

        foreach ($parameters as $parameter) {

            // 与结果取差，删掉不相交的值
            $diffIds = $result->pluck('dispatch_type_id')->diff($parameter->pluck('dispatch_type_id'));

            foreach ($result as $key => $item) {
                if ($diffIds->contains($item['dispatch_type_id'])) {
                    unset($result[$key]);
                }
            }
        }

        return $result->values();
    }


    public function getDeliveryMethodAttribute()
    {
        return $this->_gteDeliveryMethod();
    }


}
