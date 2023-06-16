<?php
/**
 * Author:  
 * Date: 2019/4/24
 * Time: 下午3:10
 */

namespace app\payment\controllers;

use app\common\helpers\Cache;
use app\common\helpers\Url;
use app\common\models\AccountWechats;
use app\common\models\finance\BalanceRecharge;
use app\common\models\Member;
use app\common\models\OrderPay;
use app\common\modules\orderGoods\OrderGoodsCollection;
use app\common\services\Pay;
use app\common\services\PayFactory;
use app\common\services\wechat\lib\WxPayConfig;
use app\common\services\wechat\lib\WxPayResults;
use app\payment\PaymentController;
use Illuminate\Support\Facades\Redis;
use Yunshop\ConvergePay\services\NotifyService;
use app\common\events\withdraw\WithdrawSuccessEvent;
use Yunshop\EplusPay\services\RequestService;
use Yunshop\StoreCashier\frontend\store\models\PreOrder;
use Yunshop\StoreCashier\frontend\store\models\PreOrderGoods;

class EplusController extends PaymentController
{
    private $attach = [];
    private $parameter = [];
    public $decode_data = [];

//    public function preAction()
//    {
//        parent::preAction();
//
//        if (empty(\YunShop::app()->uniacid)) {
//            $post = $this->getResponseResult();
//            if (\YunShop::request()->attach) {
//                \Setting::$uniqueAccountId = \YunShop::app()->uniacid = \YunShop::request()->attach['uniacid'];
//            } else {
////                $this->attach = explode(':', $post['attach']);
//                $this->attach = $post['attach'];
//                \Setting::$uniqueAccountId = \YunShop::app()->uniacid = $this->attach['uniacid'];
//            }
//        }
//    }


    public function getPayMethod()
    {
        switch ($this->decode_data['payMethod']) {
            case 'WECHATPAY_MINIPROGRAM':
                return PayFactory::EPLUS_MINI_PAY;
                break;
            case 'SCAN_ALIPAY':
                return PayFactory::EPLUS_ALI_PAY;
                break;
            default:
                return PayFactory::EPLUS_WECHAT_PAY;
        }
    }

    public function getPayMethodName()
    {
        $pay_method = $this->getPayMethod();
        switch ($pay_method) {
            case PayFactory::EPLUS_MINI_PAY:
                return '微信小程序支付(智E+)';
                break;
            case 'SCAN_ALIPAY':
                return '支付宝支付(智E+)';
                break;
            default:
                return '微信支付(智E+)';
        }
    }

    /**
     * @throws \app\common\exceptions\AppException
     * @throws \app\common\exceptions\ShopException
     * @throws \app\common\services\wechat\lib\WxPayException
     */
    public function notifyUrl()
    {
        \Log::debug('智E+后端支付回调', request()->all());
        $this->decode_data = json_decode(request()->bizContent, true);
        $this->log(request()->all());
        $pay_sn = $this->decode_data['customOrderNo'];
        \Log::debug('智E+后端支付回调单号', $pay_sn);
        $key = 'eplus_pay_result_' . $pay_sn;
        if (Redis::setnx($key, 1)) {
            Redis::expire($key, 10);
        } else {
            return;
        }


        if ($order_pay = OrderPay::where('pay_sn', $pay_sn)->first()){
            $member_id = $order_pay->uid;
        }elseif ($order_pay = BalanceRecharge::withoutGlobalScopes()->where('ordersn', $pay_sn)->first()){
            $member_id = $order_pay->member_id;
        }else{
            \Log::debug('智E+支付回调支付单号不存在', $pay_sn);
            return;
        }


        $member = Member::find($member_id);
        if (!$member) {
            \Log::debug('智E+支付回调查询会员失败', $pay_sn);
            return;
        }
        \YunShop::app()->uniacid = \Setting::$uniqueAccountId = $member->uniacid;


        $res = (new RequestService())->request('payResult', [
            'pay_sn' => $pay_sn
        ]);
        if ($res['result'] && $res['data']['payStatus'] === '00') {
            $data = [
                'total_fee' => $res['data']['amount'],
                'out_trade_no' => $pay_sn,
                'trade_no' => $res['data']['bizOrderNo'],
                'unit' => 'fen',
                'pay_type' => $this->getPayMethodName(),
                'pay_type_id' => $this->getPayMethod(),
            ];

            $this->payResutl($data);
            echo 'SUCCESS';
        }
    }

//
//    /**
//     * 支付日志
//     *
//     * @param $post
//     */
    public function log($post)
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($this->decode_data['customOrderNo'], $this->getPayMethodName(), json_encode($post));
    }


}