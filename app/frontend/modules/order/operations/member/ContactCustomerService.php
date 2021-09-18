<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2018/12/17
 * Time: 19:58
 */

namespace app\frontend\modules\order\operations\member;


use app\frontend\modules\order\operations\OrderOperation;
use app\frontend\modules\member\controllers\ServiceController;

class ContactCustomerService extends OrderOperation
{

    public function getApi()
    {

        //todo 因门店订单需要返回门店客服链接，没办法
        if (app('plugins')->isEnabled('store-cashier') && $this->order->plugin_id == 32) {
            $storeOrder = \Yunshop\StoreCashier\common\models\StoreOrder::select('store_id')->where('order_id', $this->order->id)->first();
            if ($storeOrder) {
                $customer_service = (new ServiceController())->store_set($storeOrder->store_id, request()->type);

                if ($customer_service['mark']) {
                    return $customer_service['cservice'];
                }  else {
                    $service = \Yunshop\StoreCashier\store\models\StoreService::select('service')->where('store_id', $storeOrder->store_id)->first();
                    if ($service->service) {
                        return $service->service;
                    }

                }

            }

        }

        return \Setting::get('shop.shop')['cservice'];
    }

    public function getValue()
    {
        return static::CONTACT_CUSTOMER_SERVICE;
    }

    public function getName()
    {
        return '联系客服';
    }

    public function enable()
    {
        //商品开启不可退款
        if (!$this->order->no_refund) {
            return false;
        }

        return true;
    }
}