<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/24
 * Time: 16:52
 */

namespace app\frontend\modules\payment\listeners;

use app\common\events\payment\RechargeComplatedEvent;
use app\common\services\PayFactory;

class ConvergeQuickPayListener
{
    public function onGetPaymentTypes(RechargeComplatedEvent $event)
    {
        $quickPay = \Setting::get('plugin.convergePay_set.quick_pay');

        if (\YunShop::plugin()->get('converge_pay') && !is_null($quickPay) && 1 == $quickPay['is_open']) {
            $result = [
                'name' => '汇聚快捷支付',
                'value' => PayFactory::CONVERGE_QUICK_PAY,
                'need_password' => '0'
            ];

            $event->addData($result);
        }
        return null;
    }

    public function subscribe($events)
    {
        $events->listen(
            RechargeComplatedEvent::class,
            self::class . '@onGetPaymentTypes'
        );
    }
}