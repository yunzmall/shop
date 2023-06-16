<?php

namespace app\payment\controllers;

use app\common\facades\Setting;
use app\common\models\Order;
use app\payment\PaymentController;
use Yunshop\RechargePlatform\services\OrderCallbackService;

class RechargePlatformController extends PaymentController
{
    public function notifyUrl()
    {
        $data = request()->input();
        \Log::debug('充值平台订单回调', $data);

        $order = Order::where('order_sn', $data['out_trade_num'])->withoutGlobalScopes()->first();
        if (!$order) {
            \Log::debug('充值平台订单回调，没有此订单');
            echo 'error';
            die();
        }
        \YunShop::app()->uniacid = (int)$order->uniacid;
        Setting::$uniqueAccountId = (int)$order->uniacid;

        if (app('plugins')->isEnabled('recharge-platform')) {
            $service = new OrderCallbackService($data, $order);
            if (app('plugins')->isEnabled('phone-bill-pro') and $order->plugin_id == PHONE_BILL_PRO_PLUGIN_ID) {
                return $service->phoneBillCallback();
            } elseif (app('plugins')->isEnabled('electricity-bill-pro') and $order->plugin_id == ELECTRICITY_BILL_PRO_PLUGIN_ID) {
                return $service->electricityCallback();
            }

            \Log::debug('未开启插件，plugin_id:' . $order->plugin_id);
        } else {
            \Log::debug('未开启充值平台插件');
        }

        echo 'error';
        die();
    }
}