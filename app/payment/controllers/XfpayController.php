<?php

namespace app\payment\controllers;

use app\common\models\PayOrder;
use app\payment\PaymentController;
use Yunshop\Xfpay\models\PaidRecord;
use Yunshop\Xfpay\services\Xfpay;

class XfpayController extends PaymentController
{
    protected $attach;
    protected $parameter;
    protected $hmac;
    protected $key;      //第三方加密回调key

    const SHOP_XFPAY_WECHAT = 78;
    const SHOP_XFPAY_ALIPAY = 79;

    public function __construct()
    {
        parent::__construct();
        // 接收第三方请求参数
        $this->attach = $this->validator('attach');
        $this->parameter = $this->validator('data');
        $this->hmac = $this->validator('hmac');
        $this->key = 'H357SFF768D786EBB07E1B8D9A4DABC';   // 回调key
        \YunShop::app()->uniacid = $this->getUniacid();
    }

    public function notifyUrlAlipay()
    {
        // 验证签名
        if (!$this->verifySign()){
            \Log::debug("-------- 商云客: 检测签名无效 --------");
            exit('fail');
        }

        $order_no = base64_decode($this->attach);
        $parameter = json_decode($this->parameter);

        $data = $this->setData($order_no, $parameter, self::SHOP_XFPAY_ALIPAY);

        \Log::debug('-------- 商云客支付-支付宝: 验证数据正常->更新订单状态 start --------');

        $this->payResutl($data);
        $this->buildPaidRecord($order_no, $parameter, self::SHOP_XFPAY_ALIPAY);

        \Log::debug('-------- 商云客支付-支付宝: 验证数据正常->更新订单状态 end --------');
        echo 'success';
    }

    public function notifyUrlWechat()
    {
        // 验证签名
        if (!$this->verifySign()){
            \Log::debug("-------- 商云客: 检测签名无效 --------");
            exit('fail');
        }

        $order_no = base64_decode($this->attach);
        $parameter = json_decode($this->parameter);

        $data = $this->setData($order_no, $parameter, self::SHOP_XFPAY_WECHAT);

        \Log::debug('-------- 商云客支付-微信支付: 验证数据正常->更新订单状态 start --------');

        $this->payResutl($data);
        $this->buildPaidRecord($order_no, $parameter, self::SHOP_XFPAY_WECHAT);

        \Log::debug('-------- 商云客支付-微信支付: 验证数据正常->更新订单状态 end --------');
        echo 'success';
    }

    protected function validator($param)
    {
        if (is_null($_GET[$param])){
            \Log::debug("-------- 商云客支付失败 未检测到第三方参数: $param--------");
            exit('fail');
        }

        return $_GET[$param];
    }

    protected function verifySign()
    {
        $xfpay = new Xfpay();
        $xfpay->setXfpayKey($this->key);
        $xfpay->setParams('attach', $this->attach);
        $xfpay->setParams('data', $this->parameter);
        $xfpay->getHmacParams();
        $hmac = $xfpay->getParams()['hmac'];
        return $hmac == $this->hmac;
    }

    /**
     * 支付回调操作
     * @param $data
     */
    public function payResutl($data)
    {
        try {
            $this->_payResutl($data);
            return true;

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Log::debug('回调失败:',$msg);
            echo $msg;exit();
        }
    }

    /**
     * 支付回调参数
     *
     * @param $order_no
     * @param $parameter
     * @return array
     */
    public function setData($order_no, $parameter, $pay_type_id)
    {
        return [
            'total_fee' => floatval($parameter->total_fee) * 100,
            'out_trade_no' => $order_no,
            'trade_no' => $parameter->trade_no,
            'unit' => 'fen',
            'pay_type' => '商云客聚合-微信支付',
            'pay_type_id' => $pay_type_id,
        ];
    }

    protected function getUniacid(){
        $payOrder = PayOrder::select('uniacid')->where(['out_order_no' => base64_decode($this->attach)])->first();
        if ($payOrder){
            return $payOrder->uniacid;
        }
        \Log::debug('商城订单号未找到: ',base64_decode($this->attach));
        exit('fail');
    }

    protected function buildPaidRecord($order_no, $parameter, $pay_type_id){
        try {
            PaidRecord::create([
                'uniacid' => \YunShop::app()->uniacid,
                'order_no' => $order_no,
                'trade_no' => $parameter->trade_no,
                'total_fee' => $parameter->total_fee,
                'pay_type_id' => $pay_type_id,
            ]);
        }catch (\Exception $e){
            \Log::debug('构建订单记录失败-订单号: ',base64_decode($this->attach));
        }
    }
}