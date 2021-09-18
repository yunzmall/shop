<?php
/******************************************************************************************************************
 * Author:  king -- LiBaoJia
 * Date:    6/15/21 11:03 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    www.yunzshop.com  www.yunzshop.com
 * Company: 广州市芸众信息科技有限公司
 * Profile: 专注移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务
 ******************************************************************************************************************/


namespace app\frontend\modules\payment\paymentSettings\shop;


use app\common\facades\Setting;
use Yunshop\ParentPayment\common\services\PaymentService;

class ParentPaymentSetting extends BaseSetting
{
    public function exist()
    {
        return $this->pluginState();
    }

    /**
     * 上级代付是否可以使用
     *
     * @return bool
     */
    public function canUse()
    {
        return $this->pluginState() ? $this->_canUse() : false;
    }

    /**
     * 上级代付插件开关是否开启
     *
     * @return bool
     */
    private function _canUse()
    {
        return $this->switchState() && !$this->payUid() && $this->paymentCheck() && $this->orderStatus();
    }

    /**
     * 插件订单不允许使用上级代付
     *
     * @return bool
     */
    private function orderStatus()
    {
        if ($this->orderPay) {
            return $this->orderPay->orders->contains(function ($order) {
                return !$order->plugin_id;
            });
        }
        return false;
    }

    /**
     * 会员是否可以使用上级代付
     *
     * @return bool
     */
    private function paymentCheck()
    {
        return (new PaymentService())->canUse(\YunShop::app()->getMemberId(), $this->orderPay->orders->first()->id);
    }

    /**
     * 上级代付插件开关
     *
     * @return bool
     */
    private function switchState()
    {
        return (bool)Setting::get('plugin.parent_payment.plugin_state', '0');
    }

    /**
     * 上级代付插件是否开启
     *
     * @return bool
     */
    private function pluginState()
    {
        return (bool)app('plugins')->isEnabled('parent-payment');
    }

    /**
     * 参数 pid
     *
     * @return int
     */
    private function payUid()
    {
        return (int)request()->input('pid');
    }
}
