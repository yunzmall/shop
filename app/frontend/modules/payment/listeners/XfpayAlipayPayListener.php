<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2019/6/25
 * Time: 上午 10:00
 */

namespace app\frontend\modules\payment\listeners;

use app\common\events\payment\GetOrderPaymentTypeEvent;
use app\common\events\payment\RechargeComplatedEvent;

class XfpayAlipayPayListener
{
    /**
     * 支付宝支付-商云客
     *
     * @param GetOrderPaymentTypeEvent $event
     * @return null
     */
    public function onGetPaymentTypes(GetOrderPaymentTypeEvent $event)
    {
        $set = \Setting::get('plugin.xfpay_set.xfpay');

        if (\YunShop::plugin()->get('xfpay') && !is_null($set) && 1 == $set['enabled'] && 1 == $set['pay_type']['alipay']['enabled'] && \YunShop::request()->type != 7) {
            $result = [
                'name' => '支付宝支付(商云客聚合支付)',
                'value' => '79',
                'need_password' => '0'
            ];

            $event->addData($result);
        }
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