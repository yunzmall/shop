<?php
/**
 * 单订单余额支付
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/17
 * Time: 上午10:57
 */

namespace app\frontend\modules\order\controllers;

use app\common\events\order\AfterOrderPaidRedirectEvent;
use app\common\events\payment\ChargeComplatedEvent;
use app\common\exceptions\AppException;
use app\common\services\password\PasswordService;
use app\common\services\PayFactory;
use app\frontend\models\OrderPay;
use app\frontend\modules\coupon\services\ShareCouponService;
use app\common\helpers\Url;
use app\common\models\Order;


class CreditMergePayController extends MergePayController
{
    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     * @throws \app\common\exceptions\PaymentException
     * @throws \app\common\exceptions\ShopException
     */
    public function credit2()
    {
        if (\Setting::get('shop.pay.credit') == false) {
            throw new AppException('商城未开启余额支付');
        }
        
        $this->checkPassword(\YunShop::app()->getMemberId());

        /**
         * @var OrderPay $orderPay
         */
        $orderPay = OrderPay::find(request()->input('order_pay_id'));
        // \Log::info('--orderPay', $orderPay);

        $result = $orderPay->getPayResult(PayFactory::PAY_CREDIT);
        // \Log::info('--result', $result);
        if (!$result) {
            throw new AppException('余额扣除失败,请联系客服');
        }

        try {
            // \Log::info('---step2------');
            $orderPay->pay();
            // \Log::info('---step3------');
            event(new ChargeComplatedEvent([
                'order_pay_id' => $orderPay->id
            ]));
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::debug('订单余额支付失败:', $msg);
            throw new AppException($msg);
        }

        // \Log::info('---step4----');

        $trade = \Setting::get('shop.trade');
        // \Log::info('---trade-----', $trade);

        $redirect = $admin_set_redirect = '';

        if (!is_null($trade) && isset($trade['redirect_url']) && !empty($trade['redirect_url'])) {
	        $redirect = $trade['redirect_url'];
	        preg_match("/^(http:\/\/)?([^\/]+)/i", $trade['redirect_url'], $matches);
	        $host = $matches[2];
	        // 从主机名中取得后面两段
	        preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);
	        if ($matches){//判断域名是否一致
		        $redirect = $trade['redirect_url'].'&outtradeno='.request()->input('order_pay_id');
	        }
            $admin_set_redirect = $trade['redirect_url'].'&outtradeno='.request()->input('order_pay_id');//后台设置跳转链接
        }

         $share_bool = ShareCouponService::showIndex($orderPay->order_ids, \YunShop::app()->getMemberId());

         if ($share_bool) {
             $ids = rtrim(implode('_', $orderPay->order_ids), '_');
             $redirect = Url::absoluteApp('coupon/share/'.$ids, ['i' => \YunShop::app()->uniacid, 'mid'=> \YunShop::app()->getMemberId()]);
         }
		$orders = Order::whereIn('id', $orderPay->order_ids)->get();
         event($event = new AfterOrderPaidRedirectEvent($orders,$orderPay->id));
		$redirect = $event->getData()['redirect']?:$redirect;
		return $this->successJson('成功', ['redirect' => $redirect]);
    }

    /**
     * @param int $uid
     * @return bool|void
     * @throws AppException
     * @throws \app\common\exceptions\PaymentException
     */
    protected function checkPassword($uid)
    {
        if (!$this->needPassword()) return true;

        $this->validate([
            'payment_password' => 'required'
        ]);
        return (new PasswordService())->checkPayPassword($uid, request()->input('payment_password'));
    }

    protected function needPassword()
    {
        return (new PasswordService())->isNeed('balance', 'pay');
    }
}