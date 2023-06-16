<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/11/4
 * Time: 10:22
 */

namespace app\common\modules\refund;


use app\common\models\PayType;
use app\common\services\PayFactory;

/**
 * 统一化调用退款方法返回的数据格式
 * Class RefundPayAdapter
 * @package app\common\modules\refund
 */
class RefundPayAdapter
{

    protected $pay_type_id;


    protected $pay;

    protected $pay_sn;

    protected $total_amount;

    protected $refund_amount;

    public function __construct($pay_type_id)
    {
        $this->pay_type_id = $pay_type_id;

        $this->pay =  PayFactory::create($this->pay_type_id);
    }

    protected function getPayTypeRefund()
    {
        $result = $this->pay->doRefund($this->pay_sn, $this->total_amount, $this->refund_amount);

        return $result;
    }

    /**
     * @param $pay_sn
     * @param $total_amount
     * @param $refund_amount
     * @return array|bool|mixed|string
     */
    public function pay($pay_sn, $total_amount, $refund_amount)
    {
        $this->pay_sn = $pay_sn;

        $this->total_amount = $total_amount;

        $this->refund_amount = $refund_amount;

        try {
            $result = $this->refundPay();

            if (!$result['status']) {
                \Log::debug('<---doRefund退款请求失败------'.$this->pay_sn, $result);
            }

            return $result;
        } catch (\Exception $exception) {

            \Log::debug('<---doRefund退款失败------'.$this->pay_sn, $exception->getMessage());

            return $this->fail(['pay_sn' => $pay_sn], $exception->getMessage());
        }

    }


    public function refundPay()
    {
        switch ($this->pay_type_id) {
            case PayType::WECHAT_PAY:
            case PayType::WECHAT_MIN_PAY:
            case PayType::WECHAT_H5:
            case PayType::WECHAT_NATIVE:
            case PayType::WECHAT_JSAPI_PAY:
            case PayType::WechatApp:
            case PayType::WECHAT_CPS_APP_PAY:
                $result = $this->wechat();
                break;
            case PayType::ALIPAY:
            case PayType::AlipayApp:
                $result = $this->alipay();
                break;
            case PayType::CREDIT:
                $result = $this->balance();
                break;
            case PayType::WECHAT_HJ_PAY:
            case PayType::ALIPAY_HJ_PAY:
            case PayType::CONVERGE_UNION_PAY:
                $result = $this->convergePayRefund();
                break;
            case PayType::CONVERGE_QUICK_PAY:
                $result = $this->convergeQuickPay();
                break;
            default:
                $result = $this->noAdapterType();
        }

        return $result;
    }


    //微信JSAPI、H5、NATIVE、小程序、APP支付退款入口
    protected function wechat()
    {

        $result = $this->getPayTypeRefund();

        if (!$result) {
            return $this->fail($result, '支付类退款方法失败');
        }

        return $this->success($result);
    }

    protected function alipay()
    {

        $result = $this->getPayTypeRefund();

        if ($result === false) {
            return $this->fail($result, '支付类退款方法失败');
        }

        return $this->success($result);
    }

    protected function balance()
    {
        $result = $this->getPayTypeRefund();

        if ($result !== true) {
            return $this->fail($result, '支付类退款方法失败');
        }
        return $this->success($result);
    }

    protected function convergePay()
    {

        $result = $this->getPayTypeRefund();

        if ($result['ra_Status'] == '101') {
            return $this->fail($result, '汇聚微信或支付宝退款失败，失败原因' . $result['rc_CodeMsg']);
        }

        return $this->success($result);
    }

    /**
     * 汇聚聚合支付统一退款方法
     * @return array|bool|mixed|string
     * @throws AdminException
     * @throws \app\common\exceptions\AppException
     */
    protected function convergePayRefund()
    {

        $result = $this->getPayTypeRefund();

        if ($result['ra_Status'] == '101') {
            return $this->fail($result, '汇聚退款失败，失败原因:' . $result['rc_CodeMsg']);
        }

        return $this->success($result);

    }

    protected function convergeQuickPay()
    {
        $result = $this->getPayTypeRefund();


        if (!$result['code']) {
            return $this->fail($result, $result['msg']);
        }

        return $this->success($result);

    }

    protected function noAdapterType()
    {

        $result = $this->getPayTypeRefund();

        if (!$result) {
            \Log::debug("<--{$this->pay_type_id}----没适配器支付方式退款-------".$this->pay_sn, $result);
            return $this->fail($result);
        }


        return $this->success($result);
    }

    public function fail($result,$msg = '退款请求失败')
    {
        return $this->format(0, $msg, $result);
    }

    public function success($result,$msg = '退款请求成功')
    {
        return $this->format(1, $msg, $result);
    }

    protected function format($status, $msg = '', $result)
    {
        return ['status' => $status, 'msg' => $msg, 'data' => $result];
    }
}