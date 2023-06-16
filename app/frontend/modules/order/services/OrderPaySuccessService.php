<?php

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/24
 * Time: 下午4:35
 */

namespace app\frontend\modules\order\services;

use app\backend\modules\order\services\OrderPackageService;
use app\common\events\payment\ChargeComplatedEvent;
use app\common\events\payment\RechargeComplatedEvent;
use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\models\DispatchType;
use app\common\models\Order;

use app\common\models\order\Express;
use app\common\models\order\OrderGoodsChangePriceLog;
use app\common\models\order\OrderPackage;
use app\common\models\PayOrder;
use app\common\modules\orderGoods\OrderGoodsCollection;


use \app\common\models\MemberCart;
use app\common\repositories\ExpressCompany;
use app\common\services\CreateRandomNumber;
use app\frontend\models\OrderGoods;
use app\frontend\modules\order\services\behavior\OrderCancelPay;
use app\frontend\modules\order\services\behavior\OrderCancelSend;
use app\frontend\modules\order\services\behavior\OrderChangePrice;
use app\frontend\modules\order\services\behavior\OrderClose;
use app\frontend\modules\order\services\behavior\OrderDelete;
use app\frontend\modules\order\services\behavior\OrderForceClose;
use app\frontend\modules\order\services\behavior\OrderOperation;
use app\frontend\modules\order\services\behavior\OrderPay;
use app\frontend\modules\order\services\behavior\OrderReceive;
use app\frontend\modules\order\services\behavior\OrderSend;
use app\frontend\modules\orderGoods\models\PreOrderGoods;
use app\frontend\modules\orderGoods\models\PreOrderGoodsCollection;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Yunshop\ClockIn\models\ClockPayLogModel;
use Yunshop\Gold\frontend\services\RechargeService;


class OrderPaySuccessService
{

/** 支付方式
*
* @param $order_id
* @return string
*/
    public function getPayType($order_id)
    {
        if (!empty($order_id)) {
            $tag = substr($order_id, 0, 2);
            if ('PN' == strtoupper($tag)) {
                return 'charge.succeeded';
            } elseif ('RV' == strtoupper($tag) || "RF" == strtoupper($tag) || "RL" == strtoupper($tag) || "KA" == strtoupper($tag) || "RI" == strtoupper($tag) || "RS" == strtoupper($tag) || "RI" == strtoupper($tag)) {
                return 'recharge.succeeded';
            } elseif ('RG' == strtoupper($tag)) {
                return 'gold_recharge.succeeded';
            } elseif ('CI' == strtoupper($tag)) {
                return 'card_charge.succeeded';
            } elseif ('DS' == strtoupper($tag) || "PG" == strtoupper($tag)) {
                return 'dashang_charge.succeeded';
            }
        }

        return '';
    }

    protected function _payResutl($data)
    {
        $type = $this->getPayType($data['out_trade_no']);
        $pay_order_model = PayOrder::getPayOrderInfo($data['out_trade_no'])->first();

        if ($pay_order_model) {
            $pay_order_model->status = 2;
            $pay_order_model->trade_no = $data['trade_no'];
            $pay_order_model->third_type = $data['pay_type'];
            $pay_order_model->save();
        }

        switch ($type) {
            case "charge.succeeded":
                \Log::debug("{$data['out_trade_no']}支付操作", ['charge.succeeded']);

                $orderPay = \app\common\models\OrderPay::where('pay_sn', $data['out_trade_no'])->orderBy('id', 'desc')->first();

                if ($data['unit'] == 'fen') {
                    $orderPay->amount = $orderPay->amount * 100;
                }

                if (bccomp($orderPay->amount, $data['total_fee'], 2) == 0) {
                    \Log::debug('更新订单状态');
                    OrderService::ordersPay(['order_pay_id' => $orderPay->id, 'pay_type_id' => $data['pay_type_id']]);

                    event(new ChargeComplatedEvent([
                        'order_sn' => $data['out_trade_no'],
                        'pay_sn' => $data['trade_no'],
                        'order_pay_id' => $orderPay->id
                    ]));
                } else {
                    \Log::debug("金额校验失败", "{$orderPay->amount}不等于{$data['total_fee']}");
                    throw new ShopException("金额校验失败:{$orderPay->amount}不等于{$data['total_fee']}");
                }
                break;
            case "recharge.succeeded":
                \Log::debug('支付操作', ['recharge.succeeded', $data['out_trade_no']]);

                //充值成功事件
                event(new RechargeComplatedEvent([
                    'order_sn' => $data['out_trade_no'],
                    'pay_sn' => $data['trade_no'],
                    'total_fee' => $data['total_fee'],
                    'unit' => $data['unit']
                ]));

                break;
            case "gold_recharge.succeeded":
                \Log::debug('金币支付操作', ['gold_recharge.succeeded', $data['out_trade_no']]);
                RechargeService::payResult([
                    'order_sn' => $data['out_trade_no'],
                    'pay_sn' => $data['trade_no'],
                    'total_fee' => $data['total_fee']
                ]);

                //充值成功事件
                event(new RechargeComplatedEvent([
                    'order_sn' => $data['out_trade_no'],
                    'pay_sn' => $data['trade_no'],
                    'total_fee' => $data['total_fee']
                ]));
                break;
            case "card_charge.succeeded":
                \Log::debug('打卡支付操作', ['card_charge.succeeded', $data['out_trade_no']]);

                $orderPay = ClockPayLogModel::where('order_sn', $data['out_trade_no'])->first();

                if ($data['unit'] == 'fen') {
                    $orderPay->amount = $orderPay->amount * 100;
                }

                if (bccomp($orderPay->amount, $data['total_fee'], 2) == 0) {
                    \Log::debug('更新订单状态');
                    event(new ChargeComplatedEvent([
                        'order_sn' => $data['out_trade_no'],
                        'pay_sn' => $data['trade_no'],
                        'total_fee' => $data['total_fee']
                    ]));
                }
                break;
            case "dashang_charge.succeeded":
                \Log::debug('打赏支付操作', ['dashang_charge.succeeded', $data['out_trade_no']]);
                event(new ChargeComplatedEvent([
                    'order_sn' => $data['out_trade_no'],
                    'pay_sn' => '',
                    'unit' => $data['unit'],
                    'total_fee' => $data['total_fee']
                ]));
                break;
        }
    }


    /**
     * 支付回调操作
     * @param $data
     */
    public function payResutl($data)
    {
        try {
            $this->_payResutl($data);
            return ['result' => 1, 'msg' => '成功', 'data' => []];

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::debug('回调失败:', $msg);
            return ['result' => 0, 'msg' => $msg, 'data' => []];
        }
    }
}