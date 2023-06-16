<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/18
 * Time: 11:36
 */

namespace app\frontend\modules\finance\services;


use app\common\facades\Setting;
use app\common\services\PayFactory;

class BalanceRechargeSetService
{
    private $rechargeSet;

    public function __construct()
    {
        $this->rechargeSet = Setting::get('finance.balance_recharge_set') ? : [
            'appoint_pay' => 0  //默认关闭
        ];
    }

    public function getRechargeSet()
    {
        if (!$this->rechargeSet) {
            $this->rechargeSet = Setting::get('finance.balance_recharge_set');
        }
        return $this->rechargeSet;
    }

    /**
     * 指定支付开关
     * @return bool
     */
    public function getAppointPay()
    {
        return $this->getRechargeSet()['appoint_pay'] ? true : false;
    }

    /**
     * 获取可以使用的支付code
     * @return array
     */
    public function getCanUsePayment()
    {
        $code = [];
        if ($this->getRechargeSet()['wechat']) {
            $code = array_merge($code,['wechatPay','wechatApp','wechatCpsAppPay','WechatFace','wechatH5','WechatJsapi','wechatMicroPay','wechatMinPay','wechatNative','WechatScan']);
        }
        if ($this->getRechargeSet()['alipay']) {
            $code = array_merge($code,['alipay','alipayApp','AlipayFace','AlipayJsapi','AlipayScan']);
        }
        if ($this->getRechargeSet()['pay_wechat_hj']) {
            $code = array_merge($code,['convergePayWechat']);
        }
        if ($this->getRechargeSet()['pay_alipay_hj']) {
            $code = array_merge($code,['convergePayAlipay']);
        }
        if ($this->getRechargeSet()['converge_quick_pay']) {
            $code = array_merge($code,['convergeQuickPay']);
        }
        return $code;
    }

    /**
     * 判断是否符合充值
     * @param $pay_type //支付方式
     * @param $recharge_money //充值金额
     * @return bool|string
     */
    public function verifyRecharge($pay_type,$recharge_money)
    {
        if (!$this->getAppointPay()) {
            return true;
        }
        $errStr = '不支持该支付方式进行充值';
        switch ($pay_type) {
            case  PayFactory::PAY_WEACHAT:
            case  PayFactory::PAY_Huanxun_Wx:
            case  PayFactory::PAY_APP_WEACHAT:
            case  PayFactory::PAY_CLOUD_WEACHAT:
            case  PayFactory::PAY_YUN_WEACHAT:
            case  PayFactory::PAY_WECHAT_JUEQI:
            case  PayFactory::PAY_WECHAT_SCAN_HJ:
            case  PayFactory::PAY_WECHAT_FACE_HJ:
            case  PayFactory::WECHAT_SCAN_PAY:
            case  PayFactory::WECHAT_JSAPI_PAY:
            case  PayFactory::WECHAT_H5:
            case PayFactory::WECHAT_NATIVE:
            case  PayFactory::WECHAT_CPS_APP_PAY:
            case PayFactory::WECHAT_MICRO_PAY:
            case PayFactory::WECHAT_MIN_PAY:
                if (!$this->getRechargeSet()['wechat']) {
                    return $errStr;
                }
                $max = $this->getRechargeSet()['wechat_limit'];
                $pay_name = '微信';
                break;
            case PayFactory::PAY_APP_ALIPAY:
            case PayFactory::PAY_ALIPAY:
            case PayFactory::PAY_CLOUD_ALIPAY:
            case PayFactory::PAY_YUN_ALIPAY:
            case PayFactory::ALIPAY_FACE_PAY:
            case PayFactory::ALIPAY_SCAN_PAY:
            case PayFactory::ALIPAY_JSAPI_PAY:
                if (!$this->getRechargeSet()['alipay']) {
                    return $errStr;
                }
                $max = $this->getRechargeSet()['alipay_limit'];
                $pay_name = '支付宝';
                break;
            case  PayFactory::PAY_WECHAT_HJ:
                if (!$this->getRechargeSet()['pay_wechat_hj']) {
                    return $errStr;
                }
                $max = $this->getRechargeSet()['apay_wechat_hj_limit'];
                $pay_name = '汇聚微信';
                break;
            case  PayFactory::PAY_ALIPAY_HJ:
                if (!$this->getRechargeSet()['pay_alipay_hj']) {
                    return $errStr;
                }
                $max = $this->getRechargeSet()['pay_alipay_hj_limit'];
                $pay_name = '汇聚支付宝';
                break;
            case  PayFactory::CONVERGE_QUICK_PAY:
                if (!$this->getRechargeSet()['converge_quick_pay']) {
                    return $errStr;
                }
                $max = $this->getRechargeSet()['converge_quick_pay_limit'];
                $pay_name = '汇聚快捷支付';
                break;
            case  PayFactory::LSP_PAY:
                // 基础设置没有限制
                break;
            default:
                return $errStr;
        }

        if (!empty($max) && $recharge_money > $max) {
            return '已超过' . $pay_name . '单笔最大充值金额'.$max.'元';
        }
        return true;
    }

}