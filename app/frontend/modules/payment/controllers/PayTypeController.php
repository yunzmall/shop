<?php
/**
 * Author:
 * Date: 2017/11/16
 * Time: 下午3:13
 */

namespace app\frontend\modules\payment\controllers;


use app\common\components\BaseController;
use app\common\services\PayFactory;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\order\OrderCollection;
// use app\frontend\modules\order\services\behavior\OrderPay;
use app\common\models\OrderPay;
use app\frontend\modules\payment\orderPayments\BasePayment;

class PayTypeController extends BaseController
{
    public function index()
    {
        $buttons = [];
        $filter_minPayType = [1, 3, 28, 'cashPay'];
        $client_type = \YunShop::request()->type;
        $orderPay = new OrderPay(['amount' => request()->input('price', 0.01)]);
        // todo 可以将添加订单的方法添加到收银台model中
        $order = new PreOrder(['is_virtual'=>1]);
        $orderPay->setRelation('orders',new OrderCollection([$order]));
        $paymentTypes = app('PaymentManager')->make('OrderPaymentTypeManager')->getOrderPaymentTypes($orderPay);



        $not_show = [14, 18, PayFactory::CONVERGE_QUICK_PAY, PayFactory::PAY_PAL];

         $paymentTypes->map(function (BasePayment $paymentType) {
             //余额
             if ($paymentType->getCode() == 'balance') {
                 if ($paymentType->getName() !== \Setting::get('shop.shop.credit')) {
                     $names = \Setting::get('shop.shop.credit');
                 }
             }

            return [
                'name' => $names ?: $paymentType->getName(),
                'value' => $paymentType->getId(),
                'need_password' => $paymentType->needPassword(),
            ];
        })->each(function($item, $key) use (&$buttons, $filter_minPayType, $client_type, $not_show) {
             if (!in_array($item['value'], $not_show)) {
                 switch ($client_type) {
                     case 1:
                         $buttons[] = $item;
                         break;
                     case 2:
                         if (in_array($item['value'], $filter_minPayType)) {
                             $buttons[] = $item;
                         }
                         break;
                     default:
                         $buttons[] = $item;
                 }
             }
        });

        $data = ['buttons' => $buttons];

        return $this->successJson('成功', $data);
    }
}