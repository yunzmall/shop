<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/11/2
 * Time: 下午1:40
 */

namespace app\frontend\modules\payment\orderPayments;

class CODPayment extends BasePayment
{

    public function canUse()
    {
        if (!is_null($event_arr =\app\common\modules\shop\ShopConfig::current()->get('forbid_delivery_pay'))) {
            foreach ($event_arr as $v){
                $class = array_get($v, 'class');
                $function = array_get($v, 'function');
                if ($class::$function(request()->order_ids)) {
                    return false;
                }
            }
        }

        return parent::canUse() && !$this->hasVirtual();
    }

    private function hasVirtual()
    {
        foreach ($this->orderPay->orders as $order){
            if($order->isVirtual()){
                return true;
            }
        }
        return false;
    }
}