<?php

namespace app\frontend\modules\payment\listeners;

use app\common\events\payment\GetOrderPaymentTypeEvent;
use app\common\services\password\PasswordService;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/5/17
 * Time: 下午5:44
 */
class Credit
{
    public function onGetPaymentTypes(GetOrderPaymentTypeEvent $event)
    {
        if (\Setting::get('shop.pay.credit')) {
            $result = [
                'name'          => '余额',
                'value'         => '3',
                'need_password' => $this->needPassword()
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
    }

    private function needPassword()
    {
        return (new PasswordService())->isNeed('balance', 'pay');
    }
}