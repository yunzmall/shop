<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/6/13 下午2:22
 * Email: livsyitian@163.com
 */

namespace app\frontend\modules\withdraw\services;


use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\models\McMappingFans;
use app\frontend\modules\finance\services\WithdrawManualService;
use app\frontend\modules\withdraw\models\Withdraw;

class PayWayValidatorService
{
    public function validator($pay_way)
    {
        //todo 临时使用，应该提供一个系统的验证
        switch ($pay_way) {
            case 'balance':
                $this->balanceValidator();
                break;
            case 'wechat':
                $this->weChatValidator();
                break;
            case 'alipay':
                $this->alPayValidator();
                break;
            case 'huanxun':
                $this->huanXunValidator();
                break;
            case 'manual':
                $this->manualValidator();
                break;
            case 'eup_pay':
                $this->eupPayValidator();
                break;
            case 'yop_pay':
                $this->yopPayValidator();
                break;
            case 'converge_pay':
                $this->convergePayValidator();
                break;
            case 'yee_pay':
                $this->yeePayValidator();
                break;
            case 'converge-alloc-funds':
                $this->convergeSeparatePayValidator();
                break;
            case 'high_light_wechat':
                $this->highLightValidator('high_light_wechat');
                break;
            case 'high_light_alipay':
                $this->highLightValidator('high_light_alipay');
                break;
            case 'high_light_bank':
                $this->highLightValidator('high_light_bank');
                break;
            default:
                throw new AppException('未知提现方式');
                break;
        }
    }


    private function convergeSeparatePayValidator(){

    }

    private function balanceValidator()
    {

    }

    private function weChatValidator()
    {

    }

    private function alPayValidator()
    {
        if (!WithdrawManualService::getAlipayStatus()) {
            throw new AppException('您未配置支付宝信息，请先修改个人信息中支付宝信息', ['status' => 1]);
        }
    }


    private function huanXunValidator()
    {

    }


    private function eupPayValidator()
    {

    }

    private function yopPayValidator()
    {

    }

    private function convergePayValidator()
    {

    }


    private function manualValidator()
    {
        switch ($this->getManualType()) {
            case Withdraw::MANUAL_TO_WECHAT:
                $result = $this->weChatStatus();
                break;
            case Withdraw::MANUAL_TO_ALIPAY:
                $result = $this->alipayStatus();
                break;
            default:
                $result = $this->bankStatus();
        }
        if ($result !== true) {
            throw new AppException($result, ['status' => 1]);
        }
    }

    private function yeePayValidator()
    {
        if (!app('plugins')->isEnabled('yee-pay')) {
            throw new AppException('易宝代付插件未开启');
        }
        $employee = \Yunshop\YeePay\services\EmployeeService::employee([
            'member_id' => \YunShop::app()->getMemberId()
        ])->first();
        if (!$employee) {
            throw new AppException('您未完成易宝代付签约，暂不能进行提现', ['yee_pay' => 1]);
        }
        return true;
    }

    private function highLightValidator($type)
    {
        if (!app('plugins')->isEnabled('high-light') || !\Yunshop\HighLight\services\SetService::getStatus()) {
            throw new AppException('高灯提现插件未开启');
        }
        try {
            $agreementInfo = \Yunshop\HighLight\services\AgreementService::agreementInfo(['member_id'=>\Yunshop::app()->getMemberId()])->first();
            if (!$agreementInfo || !\Yunshop\HighLight\services\AgreementService::checkAgreement($agreementInfo)) {
                $is_check = false;
            } else {
                $is_check = true;
            }
        } catch (\Exception $e) {
            throw new AppException($e->getMessage());
        }
        if (!$is_check) {
            throw new AppException('您未完成高灯签约，暂不能进行提现', ['high_light' => 1]);
        }
        switch ($type) {
            case 'high_light_wechat':
                $fans = McMappingFans::where('uid',\Yunshop::app()->getMemberId())->first();
                if (!$fans) {
                    throw new AppException('您未在公众号商城中授权登录过，无法进行高灯微信提现');
                }
                break;
            case 'high_light_alipay':
                if (!$agreementInfo->payment_account) {
                    throw new AppException('请您填写好所要提现到的支付宝账号', ['high_light' => 1]);
                }
                break;
            case 'high_light_bank':
                if (!$agreementInfo->bank_name || !$agreementInfo->bankcard_num) {
                    throw new AppException('请您填写好所要提现到的银行信息', ['high_light' => 1]);
                }
                break;
            default:
                throw new AppException('未知提现类型');
        }
    }

    /**
     * 是否配置银行卡信息
     * @return bool|string
     */
    private function bankStatus()
    {
        if (!WithdrawManualService::getBankStatus()) {
            return '请先完善您个人信息中银行卡信息';
        }
        return true;
    }


    /**
     * 是否配置微信信息
     * @return bool|string
     */
    private function weChatStatus()
    {
        if (!WithdrawManualService::getWeChatStatus()) {
            return '请先完善您个人信息中的微信信息';
        }
        return true;
    }


    /**
     * 是否配置支付宝信息
     * @return bool|string
     */
    private function alipayStatus()
    {
        if (!WithdrawManualService::getAlipayStatus()) {
            return '请先完善您个人信息中支付宝信息';
        }
        return true;
    }


    private function getManualType()
    {
        $set = Setting::get('withdraw.income');

        return empty($set['manual_type']) ? 1 : $set['manual_type'];
    }


}
