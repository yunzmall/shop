<?php

namespace app\common\modules\refund\services;

use app\backend\modules\refund\models\RefundApply;
use app\backend\modules\refund\services\RefundOperationService;
use app\common\events\order\BeforeOrderApplyRefundedEvent;
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
 * Author:
 * Date: 2017/5/10
 * Time: 下午4:29
 */
class RefundService
{
    protected $refundApply;

    public function fastRefund($order_id)
    {
        $order = Order::find($order_id);
        return RefundOperationService::orderCloseAndRefund($order);
    }

    public function pay($refund_id)
    {
        $this->refundApply = RefundApply::find($refund_id);

        if (!isset($this->refundApply)) {
            throw new AdminException('未找到退款记录');
        }

        if (bccomp($this->refundApply->price, 0, 2) !== 1) {
            throw new AdminException('退款金额为0，请使用：手动退款');
        }

        if ($this->refundApply->status < RefundApply::WAIT_CHECK) {
            throw new AdminException($this->refundApply->status_name . '的退款申请,无法执同意退款操作');
        }

        if (in_array($this->refundApply->status,[RefundApply::COMPLETE,RefundApply::CONSENSUS])) {
            throw new AdminException('已退款,无法执同意退款操作');
        }

        event(new BeforeOrderApplyRefundedEvent($this->refundApply->order));

        //订单锁定时不能退款
        if ($this->refundApply->order->isPending()) {
            event(new BeforeOrderRefundedPendingEvent($this->refundApply->order));
            if ($this->refundApply->order->isPending()){
                throw new AdminException("订单已锁定,无法继续操作");
            }
        }

         //必须保证请求支付退款接口成功才能改变售后状态
        //如果先改变退款状态会触发退款成功监听，实际请求支付退款接口失败了
        switch ($this->refundApply->order->pay_type_id) {
            case PayType::WECHAT_PAY:
            case PayType::WECHAT_MIN_PAY:
            case PayType::WECHAT_H5:
            case PayType::WECHAT_NATIVE:
            case PayType::WECHAT_JSAPI_PAY:
            case PayType::WECHAT_SCAN_PAY:
                $result = $this->wechat();
                break;
            case PayType::ALIPAY:
            case PayType::ALIPAY_JSAPI_PAY:
                $result = $this->alipay2();
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
            case PayType::WECHAT_CPS_APP_PAY:
                $result = $this->wechat();
                break;
            case PayType::XFPAY_WECHAT:
            case PayType::XFPAY_ALIPAY:
                $result = $this->xfpayPay();
                break;
            case PayType::SANDPAY_ALIPAY:
            case PayType::SANDPAY_WECHAT:
                $result = $this->sandpayPay();
                break;
            case PayType::LAKALA_ALIPAY:
            case PayType::LAKALA_WECHAT:
                $result = $this->lakalaPay();
                break;
            case PayType::LESHUA_ALIPAY:
            case PayType::LESHUA_WECHAT:
            case PayType::LESHUA_POS:
                $result = $this->leshuaPay();
                break;
            case PayType::LSP_PAY:
                $result = $this->lspPay();
                break;
            case PayType::WECHAT_TRADE_PAY:
                $result = $this->wechatTradePay();
                break;
            case PayType::CONVERGE_UNION_PAY:
                $result = $this->convergePayRefund();
                break;
            case PayType::SILVER_POINT_ALIPAY:
            case PayType::SILVER_POINT_WECHAT:
            case PayType::SILVER_POINT_UNION:
                $result = $this->silverPointRefund();
                break;
            case PayType::CODE_SCIENCE_PAY_YU:
                $result = $this->codeScienceRefund();
                break;
            case PayType::EPLUS_WECHAT_PAY:
            case PayType::EPLUS_MINI_PAY:
            case PayType::EPLUS_ALI_PAY:
                $result = $this->eplusPay();
                break;
            case PayType::LSP_WALLET_PAY:
                $result = $this->lspWalletPay();
                break;
            case PayType::JINEPAY:
                $result = $this->jinepayRefund();
                break;
            case PayType::AUTH_PAY:
                $result = $this->authPayRefund();
                break;
            case PayType::VIDEO_SHOP_PAY:
                $result = $this->videoShopPay();
                break;
            default:
                $result = $this->unknownPay();
        }

        return $result;
    }

    private function unknownPay()
    {
        \Log::debug('------售后确认退款支付类型无对应退款方法--'.$this->refundApply->order->pay_type_id,[$this->refundApply->order->order_sn]);
        $payAdapter = new \app\common\modules\refund\RefundPayAdapter($this->refundApply->order->pay_type_id);

        $result =  $payAdapter->pay($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result['status']) {
            throw new AdminException($result['msg']);
        }

        //微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return true;
    }

    private function eplusPay(){
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('智E+退款失败');
        }

        //微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function videoShopPay(){
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);
        //dd([$this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price]);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('视频号小店退款失败');
        }

        //微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function wechatTradePay(){
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);
        //dd([$this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price]);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('微信视频号退款失败');
        }

        //微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    //微信JSAPI、H5、NATIVE、小程序、APP支付退款入口
    private function wechat()
    {

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);
        //dd([$this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price]);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('微信退款失败');
        }

        //微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

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

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result === false) {
            throw new AdminException('支付宝退款失败');
        }

        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function alipayapp()
    {

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result === false) {
            throw new AdminException('支付宝退款失败');
        }

        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

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

    private function lspPay()
    {
        $refundApply = $this->refundApply;
        //退款状态设为完成
        RefundOperationService::refundComplete(['id' => $refundApply->id]);
//        $order = Order::select('id', 'order_pay_id')
//            ->with([
//                'hasOneOrderPay'
//            ])
//            ->find($refundApply->order_id);
//        if (!$order || !$order->hasOneOrderPay) {
//            throw new AdminException("加速池支付:未找到订单");
//        }
        $order = $refundApply->order;
        $orderPay = $order->hasOneOrderPay;
        $pay = PayFactory::create($order->pay_type_id);
        //$lspPay = new LSPPay();
        return $pay->doRefund($orderPay->pay_sn, $orderPay->amount, $refundApply->price);
    }

    private function lspWalletPay(){
        $refundApply = $this->refundApply;
        $pay = PayFactory::create($refundApply->order->pay_type_id);

        $result = $pay->doRefund($refundApply->order->hasOneOrderPay->pay_sn, $refundApply->order->hasOneOrderPay->amount, $refundApply->price);

        if (!$result) {
            throw new AdminException('爱心值加速池钱包退款失败');
        }

        //退款状态设为完成
        RefundOperationService::refundComplete(['id' => $refundApply->id]);

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
            'relation'     => $refundApply->refund_sn,
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
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('芸支付微信退款失败');
        }

        //芸支付微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

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

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('环迅快捷退款失败');
        }

        //环迅快捷退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function hxwechat()
    {
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('环迅微信退款失败');
        }

        //环迅微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function ConvergeWechat()
    {

        $pay = PayFactory::create(PayFactory::PAY_WECHAT_HJ); //修复支付宝无法退款，退款处理统一写在了汇聚微信支付类里了

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn,
            $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result['ra_Status'] == '101') {
            \Log::debug('汇聚微信或支付宝退款失败，失败原因' . $result['rc_CodeMsg'] . '-----失败参数-----' . json_encode($result));
            throw new AdminException('汇聚微信或支付宝退款失败，失败原因' . $result['rc_CodeMsg'] . '-----失败参数-----' . json_encode($result));
        }

        //汇聚微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

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

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('微信退款失败');
        }

        //港版微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

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

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('退款失败');
        }

        //港版微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function xfpayPay()
    {
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('商云客退款失败');
        }

        //商云客微信支付宝退款
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function sandpayPay()
    {

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('杉德退款失败');
        }

        // 杉德支付宝微信退款
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function lakalaPay()
    {

        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('拉卡拉支付退款失败');
        }

        // 拉卡拉支付宝微信退款
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function leshuaPay()
    {
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('乐刷支付退款失败');
        }

        // 拉卡拉支付宝微信退款
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function silverPointRefund()
    {
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('银典支付退款失败');
        }

        // 银典支付支付宝微信退款
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function codeScienceRefund()
    {
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('豫章行代金券支付退款失败');
        }

        // 银典支付支付宝微信退款
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

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


    /**
     * 汇聚聚合支付统一退款方法
     * @return array|bool|mixed|string
     * @throws AdminException
     * @throws \app\common\exceptions\AppException
     */
    protected function convergePayRefund()
    {

        $pay = PayFactory::create($this->refundApply->order->pay_type_id); //修复支付宝无法退款，退款处理统一写在了汇聚微信支付类里了

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn,
            $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if ($result['ra_Status'] == '101') {
            \Log::debug('汇聚退款失败，失败原因' . $result['rc_CodeMsg'] . '-----失败参数-----' . json_encode($result));
            throw new AdminException('汇聚支付退款失败，失败原因' . $result['rc_CodeMsg'] . '-----失败参数-----' . json_encode($result));
        }

        //汇聚微信退款 同步改变退款和订单状态
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function jinepayRefund()
    {
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('锦银E付退款失败');
        }

        // 银典支付支付宝微信退款
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }

    private function authPayRefund()
    {
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);

        if (!$result) {
            throw new AdminException('微信借权支付退款失败');
        }

        // 银典支付支付宝微信退款
        RefundOperationService::refundComplete(['id' => $this->refundApply->id]);

        return $result;
    }
}