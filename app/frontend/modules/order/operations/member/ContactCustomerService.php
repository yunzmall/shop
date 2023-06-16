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
use Yunshop\Supplier\admin\models\Supplier;
use Yunshop\Supplier\common\models\SupplierOrder;

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

        if (app('plugins')->isEnabled('supplier') && $this->order->plugin_id == 92) {
            $supplierOrder = SupplierOrder::select('supplier_id')->where('order_id', $this->order->id)->first();
            if ($supplierOrder) {
                $supplier = Supplier::getSupplierById($supplierOrder->supplier_id);
                $supplierSet = (new ServiceController())->supplier_set($supplier->uid, request()->type);
                //先将门店单独客服设置的cservice取出
                if($supplierSet['cservice']) {
                    return $supplierSet['cservice'];
                }
            }
        }

        //新加客服插件
        if (app('plugins')->isEnabled('customer-service')) {
            $set = array_pluck(\Setting::getAllByGroup('customer-service')->toArray(), 'value', 'key');
            if ($set['is_open'] == 1) {
                if (request()->type == 2) {
                    return $set['mini_link'];
                } else {
                    return $set['link'];
                }
            }
        }

        if (request()->type == 2) {
            return \Setting::get('shop.shop')['cservice_mini'];
        } else {
            return \Setting::get('shop.shop')['cservice'];
        }
    }

    public function getValue()
    {
        return static::CONTACT_CUSTOMER_SERVICE;
    }

    public function getName()
    {
        if ($this->order->uid == \YunShop::app()->getMemberId()) {
            return '联系客服';
        }
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