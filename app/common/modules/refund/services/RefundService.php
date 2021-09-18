<?php

namespace app\common\modules\refund\services;

use app\backend\modules\refund\models\RefundApply;
use app\backend\modules\refund\services\RefundOperationService;
use app\common\events\order\BeforeOrderRefundedPendingEvent;
use app\common\exceptions\AdminException;
use app\common\facades\Setting;
use app\common\models\finance\Balance;
use app\common\models\Order;
use app\common\models\PayType;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\services\PayFactory;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/5/10
 * Time: 下午4:29
 */
class RefundService
{
    protected $refundApply;

    public function fastRefund($order_id)
    {
        $order = Order::find($order_id);
        $refundApply = \app\common\models\refund\RefundApply::createByOrder($order);
        $refundApply->save();
        return $this->pay($refundApply->id);
    }

    public function pay($refund_id)
    {
        $this->refundApply = RefundApply::find($refund_id);

        if (!isset($this->refundApply)) {
            throw new AdminException('未找到退款记录');
        }
        //订单锁定时不能退款
        if ($this->refundApply->order->isPending()) {
            event(new BeforeOrderRefundedPendingEvent($this->refundApply->order));
            if ($this->refundApply->order->isPending()){
                throw new AdminException("订单已锁定,无法继续操作");
            }
        }


        switch ($this->refundApply->order->pay_type_id) {
            case PayType::WECHAT_PAY:
            case PayType::WECHAT_MIN_PAY:
            case PayType::WECHAT_H5:
            case PayType::WECHAT_NATIVE:
            case PayType::WECHAT_JSAPI_PAY:
                $result = $this->wechat();
                break;
            case PayType::ALIPAY:
                $set = \Setting::get('shop.pay');
                if (isset($set['alipay_pay_api']) && $set['alipay_pay_api'] == 1) {
                    $result = $this->alipay2();
                } else {
                    $result = $this->alipay();
                }
                break;
            case PayType::CREDIT:
                $result = $this->balance();
                break;
            case PayType::BACKEND:
                $result = $this->backend();
                break;
            case PayType::WechatApp:
                $result = $this->wechat();
                break;
            case PayType::AlipayApp:
                $result = $this->alipayapp();
                break;
            case PayType::PAY_YUN_WECHAT:
                $result = $this->yunWechat();
                break;
            case PayType::HXQUICK:
                $result = $this->hxquick();
                break;
            case PayType::HXWECHAT:
                $result = $this->hxwechat();
                break;
            case PayType::STORE_PAY:
                $result = $this->backend();
                break;
            case PayType::YOP:
                $result = $this->yopWechat();
                break;
            case PayType::WECHAT_HJ_PAY:
                $result = $this->ConvergeWechat();
                break;
            case PayType::ALIPAY_HJ_PAY:
                $result = $this->ConvergeWechat();
                break;
            case PayType::PAY_TEAM_DEPOSIT:
                $result = $this->deposit();
                break;
            case PayType::LCG_BALANCE:
            case PayType::LCG_BANK_CARD:
                $result = $this->lcg();
                break;
            case PayType::YOP_PRO_WECHAT:
            case PayType::YOP_PRO_ALIPAY:
                $result = $this->yopPro();
                break;
            case PayType::HK_SCAN_PAY:
                $result = $this->hkPay();
                break;
            case PayType::PAY_PAL:
                $result = $this->payPal();
                break;
            case PayType::CONVERGE_QUICK_PAY:
                $result = $this->convergeQuickPay();
                break;
            case PayType::HK_SCAN_ALIPAY:
                $result = $this->hkAliPay();
                break;
            case PayType::CONFIRM_PAY:
                $result = $this->confirmPay();
                break;
            case PayType::STORE_AGGREGATE_WECHAT:
                $result = $this->storeAggregatePay();
                break;
            case PayType::STORE_AGGREGATE_ALIPAY:
                $result = $this->storeAggregatePay();
                break;
            case PayType::STORE_AGGREGATE_SCAN:
                $result = $this->storeAggregatePay();
                break;
            default:
                $result = false;
                break;
            case PayType::WECHAT_CPS_APP_PAY:
                $result = $this->wechat();
                break;
            case PayType::XFPAY_WECHAT:
            case PayType::XFPAY_ALIPAY:
                $result = $this->xfpayPay();
                break;
        }

        return $result;
    }

    //微信JSAPI、H5、NATIVE、小程序、APP支付退款入口
    private function wechat()
    {
        //微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);
        //dd([$this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price]);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('微信退款失败');
        }
        return $result;
    }

    private function wechatH5()
    {
        //微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);
        //dd([$this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price]);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('微信退款失败');
        }
        return $result;
    }

    private function alipay()
    {
        //RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result === false) {
            throw new AdminException('支付宝退款失败');
        }
        //保存batch_no,回调成功后根据batch_no找到对应的退款记录
        $this->refundApply->alipay_batch_sn = $result['batch_no'];
        $this->refundApply->save();
        return $result['url'];
    }

    private function alipay2()
    {
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result === false) {
            throw new AdminException('支付宝退款失败');
        }
        return $result;
    }

    private function alipayapp()
    {
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result === false) {
            throw new AdminException('支付宝退款失败');
        }
        return $result;
    }

    private function backend()
    {
        $refundApply = $this->refundApply;
        //退款状态设为完成
        $result = RefundOperationService::refundComplete(['id' => $refundApply->id]);

        if ($result !== true) {
            throw new AdminException($result);
        }
        return $result;
    }

    private function yopWechat()
    {
        $result = PayFactory::create($this->refundApply->order->pay_type_id)->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result !== true) {
            throw new AdminException($result);
        }

        //退款状态设为完成
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function deposit()
    {
        $result = PayFactory::create($this->refundApply->order->pay_type_id)->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result !== true) {
            throw new AdminException(TEAM_REWARDS_DEPOSIT . '退款失败');
        }

        //退款状态设为完成
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function balance()
    {
        $refundApply = $this->refundApply;
        //退款状态设为完成
        RefundOperationService::refundComplete(['id' => $refundApply->id]);

        $data = [
            'member_id'    => $refundApply->uid,
            'remark'       => '订单(ID' . $refundApply->order->id . ')余额支付退款(ID' . $refundApply->id . ')' . $refundApply->price,
            'source'       => ConstService::SOURCE_CANCEL_CONSUME,
            'relation'     => $refundApply->order->order_sn,
            'operator'     => ConstService::OPERATOR_ORDER,
            'operator_id'  => $refundApply->uid,
            'change_value' => $refundApply->price
        ];
		if ($refundApply->order->hasOneOrderPay->behalfPay) {
			$data['member_id'] = $refundApply->order->hasOneOrderPay->behalfPay->behalf_id;
			$data['remark'] .= "（代付退款）";
		}
        $result = (new BalanceChange())->cancelConsume($data);


        if ($result !== true) {
            throw new AdminException($result);
        }
        return $result;
    }

    private function yunWechat()
    {
        //芸支付微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('芸支付微信退款失败');
        }
        return $result;
    }

    public static function allowRefund()
    {
        $refund_status = Setting::get('shop.trade.refund_status');
        if ($refund_status == 1 || $refund_status == null) {
            return true;
        }
    }

    private function hxquick()
    {
        //环迅快捷退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('环迅快捷退款失败');
        }
        return $result;
    }

    private function hxwechat()
    {
        //环迅微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('环迅微信退款失败');
        }
        return $result;
    }

    private function ConvergeWechat()
    {
        //汇聚微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        $pay = PayFactory::create(PayFactory::PAY_WECHAT_HJ); //修复支付宝无法退款，退款处理统一写在了汇聚微信支付类里了

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn,
            $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result['ra_Status'] == '101') {
            \Log::debug('汇聚微信或支付宝退款失败，失败原因' . $result['rc_CodeMsg'] . '-----失败参数-----' . json_encode($result));
            throw new AdminException('汇聚微信或支付宝退款失败，失败原因' . $result['rc_CodeMsg'] . '-----失败参数-----' . json_encode($result));
        }
        return $result;
    }

    /**
     * 龙存管-支付退款
     * @return array
     * @throws AdminException
     * @throws \app\common\exceptions\AppException
     */
    protected function lcg()
    {
        $pay_sn = $this->refundApply->order->hasOneOrderPay->pay_sn;
        $amount = $this->refundApply->order->hasOneOrderPay->amount;
        $result = PayFactory::create($this->refundApply->order->pay_type_id)->doRefund($pay_sn, $amount, $this->refundApply->price, $this->refundApply->order->order_sn);

        if ($result['code'] !== true) {
            throw new AdminException($result['msg']);
        }
        //退款状态设为完成
        //RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        //throw new AdminException('退款申请成功，等待商家确认退款中。。。');

        return ['action' => $result['data']['action_url'], 'input' => $result['data']['form_data']];

        //return $result['code'];
    }

    /**
     * 易宝专业版 微信、支付宝退款
     * @return array|bool|mixed|void
     * @throws AdminException
     * @throws \app\common\exceptions\AppException
     */
    private function yopPro()
    {
        $pay_sn = $this->refundApply->order->hasOneOrderPay->pay_sn;
        $amount = $this->refundApply->order->hasOneOrderPay->amount;
        $result = PayFactory::create($this->refundApply->order->pay_type_id)->doRefund($pay_sn, $amount, $this->refundApply->price, $this->refundApply->order->order_sn);

        if ($result !== true) {
            throw new AdminException($result);
        }
        //退款状态设为完成
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    /**
     * 港版微信退款
     * @return array|bool|mixed|void
     * @throws AdminException
     * @throws \app\common\exceptions\AppException
     */
    private function hkPay()
    {
        //港版微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('微信退款失败');
        }
        return $result;
    }


    private function payPal()
    {
        $pay_sn = $this->refundApply->order->hasOneOrderPay->pay_sn;
        $amount = $this->refundApply->order->hasOneOrderPay->amount;
        $result = PayFactory::create($this->refundApply->order->pay_type_id)->doRefund($pay_sn, $amount, $this->refundApply->price);

        if ($result !== true) {
            throw new AdminException('退款失败');
        }
        //退款状态设为完成
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        return $result;

    }

    private function convergeQuickPay()
    {
        $pay_sn = $this->refundApply->order->hasOneOrderPay->pay_sn;
        $amount = $this->refundApply->order->hasOneOrderPay->amount;
        $result = PayFactory::create($this->refundApply->order->pay_type_id)->doRefund($pay_sn, $amount, $this->refundApply->price);


        if (!$result['code']) {
            throw new AdminException($result['msg']);
        }
        //同步改变退款和订单状态
        //RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        return true;
    }

    /**
     * 港版支付宝H5退款
     * @return array|bool|mixed|void
     * @throws AdminException
     * @throws \app\common\exceptions\AppException
     */
    private function hkAliPay()
    {
        //港版微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('退款失败');
        }
        return $result;
    }

    private function xfpayPay()
    {
        //商云客微信支付宝退款
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('退款失败');
        }
        return $result;
    }

    /**
     * 确认支付退款操作
     * @return bool
     * @throws AdminException
     */
    private function confirmPay()
    {
        //改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return true;

        //$pay = PayFactory::create($this->refundApply->order->pay_type_id);
        //$pay_sn = $this->refundApply->order->hasOneOrderPay->pay_sn;
        //$amount = $this->refundApply->order->hasOneOrderPay->amount;
        //$result = $pay->doRefund($pay_sn, $amount, $this->refundApply->price);

    }

    public function storeAggregatePay()
    {
        $pay_sn = $this->refundApply->order->hasOneOrderPay->pay_sn;
        $amount = $this->refundApply->order->hasOneOrderPay->amount;
        $result = PayFactory::create($this->refundApply->order->pay_type_id)->doRefund($pay_sn, $amount, $this->refundApply->price);

        //同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);
        return true;
    }
}