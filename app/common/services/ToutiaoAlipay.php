<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/23
 * Time: 上午11:03
 */

/**
 * 手机WAP端支付宝支付功能
 */
namespace app\common\services\alipay;

use app\common\exceptions\AppException;
use app\common\services\AliPay;

class ToutiaoAlipay extends AliPay
{
    public function __construct()
    {
        //todo
    }

    public function doPay($data = [],$payType = 52)
    {

        $isnewalipay = \Setting::get('shop.pay.alipay_pay_api');
        if (isset($isnewalipay) && $isnewalipay == 1) {
            \Log::info('-------test-------', print_r($data,true));
            $uniacid = substr($data['body'], strrpos($data['body'], ':')+1);
            $content = [
                'body' => $uniacid,
                'subject' => $data['subject'],
                'out_trade_no' => \YunShop::app()->uniacid.'_'.$data['order_no'],
                'total_amount' => $data['amount'],
                'product_code' => 'QUICK_MSECURITY_PAY',
            ];
            // 跳转到支付页面。
            $result = app('alipay.toutiao')->sdkExecute(json_encode($content));

            return $result;
        }
        throw new AppException('未开启支付宝新接口');
    }
}