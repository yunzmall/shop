<?php

namespace app\frontend\modules\payment\listeners;

use app\common\events\payment\GetOrderPaymentTypeEvent;
use app\common\events\payment\RechargeComplatedEvent;
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/5/17
 * Time: 下午5:44
 */
class DcmScanPay
{
    public function onGetPaymentTypes(GetOrderPaymentTypeEvent $event)
    {
        $result = [
            'name' => 'dcm扫码支付',
            'value' => '66',
            'need_password' => '0'

        ];
        $event->addData($result);

        return null;
    }

    public function subscribe($events)
    {
        $events->listen(
            GetOrderPaymentTypeEvent::class,
            self::class . '@onGetPaymentTypes'
        );

        $events->listen(
            RechargeComplatedEvent::class,
            self::class . '@onGetPaymentTypes'
        );
    }
}