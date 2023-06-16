<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022/6/28
 * Time: 17:20
 */

namespace app\common\listeners;


use app\common\events\WechatMessage;
use app\common\models\WechatMinAppPayOrder;
use app\common\services\Pay;
use app\common\services\PayFactory;
use app\common\services\payment\WechatMinPay;
use app\common\services\wechat\WechatPayEventNoticeHandle;

class WechatMinPayNotifyListener
{

    public function handle(WechatMessage $event)
    {
        //获取微信对象

        $message = $event->getMessage();


        ///小程序支付管理 支付成功通知
        if ($message['MsgType'] == 'event' && $message['Event'] == 'funds_order_pay') {
            \Log::debug('<---小程序支付管理--支付成功通知', $message);
            $this->paySuccessCallback($message);
        }

        //小程序支付管理 退款成功通知
        if ($message['MsgType'] == 'event' && $message['Event'] == 'funds_order_refund') {
            \Log::debug('<---小程序支付管理--退款通知', $message);
            $this->refundCallback($message);
        }

    }

    public function paySuccessCallback($message)
    {
        if (!$message['order_info']['trade_no']) {

            echo '微信小程序支付事件通知商家交易单号不存在';exit();
        }



        $this->log($message['order_info']['trade_no'], $message);

        $orderInfo = (new WechatMinPay())->selectOrder($message['order_info']['trade_no']);



        $this->recordDivideLog($message,$orderInfo);


        $data = [
            'total_fee' => $orderInfo['amount'],
            'out_trade_no' => $orderInfo['trade_no'],
            'trade_no' => $orderInfo['transaction_id'],
            'unit' => 'fen',
            'pay_type' => '微信小程序',
            'pay_type_id' => PayFactory::WECHAT_MIN_PAY,
        ];

        $res = (new WechatPayEventNoticeHandle())->payNotify($data);

        \Log::debug('---小程序支付管理--支付成功通知--->', $res);

        echo $res['code']? 'success' : $res['msg'];exit();
    }

    /**
     * 支付日志
     *
     * @param $post
     */
    public function log($trade_no, $message)
    {
        //访问记录
        Pay::payAccessLog();
        //保存响应数据
        Pay::payResponseDataLog($trade_no, '微信小程序', json_encode($message));
    }

    public function refundCallback($message)
    {


    }

    //记录支付日志
    public function recordDivideLog($message,$orderInfo)
    {
        $payRecord = WechatMinAppPayOrder::existOrNew($message['order_info']['trade_no']);



        $payRecord->fill([
            'trade_no' => $message['order_info']['trade_no'],
            'transaction_id' => $message['order_info']['transaction_id'],
            'notice_params' => array_merge($message['order_info'], ['amount' => $orderInfo['amount']]),
            'pay_time' =>  $message['CreateTime'],
            'status' => 1,
        ]);

        $payRecord->save();
    }
}