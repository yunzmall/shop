<?php
/**
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/6/8
 * Time: 下午5:49
 */

namespace app\common\services\finance;


use app\common\facades\Setting;

class IncomeService
{
    private static $payWay = [
        'balance',
        'wechat',
        'alipay',
        'manual',
        'huanxun',
        'eup_pay',
        'yop_pay',
        'converge_pay'
    ];

    private static $payWayName = [
        'balance'      => "提现到余额",
        'wechat'       => "提现到微信",
        'alipay'       => "提现到支付宝",
        'manual'       => "提现到手动打款",
        'huanxun'      => "提现到银行卡",
        'eup_pay'      => "提现到EUP",
        'yop_pay'      => "提现到易宝",
        'converge_pay' => "提现到银行卡-HJ",
    ];

    public function withdrawButton($incomeType = 'default')
    {
        switch($incomeType)
        {
            case 'StoreCashier':
                return $this->storeCashierButton();
            case 'StoreWithdraw':
                return $this->storeWithdrawButton();
            case 'StoreBossWithdraw':
                return $this->storeBossWithdrawButton();
            default:
                return $this->defaultButton();
        }
    }

    /**
     * 门店收银台提现方式按钮组
     *
     * @return array
     */
    private function storeCashierButton()
    {
        return $this->customButton('StoreCashier');
    }

    /**
     * 门店提现方式按钮组
     *
     * @return array
     */
    private function storeWithdrawButton()
    {
        return $this->customButton('StoreWithdraw');
    }

    /**
     * 连锁店现方式按钮组
     *
     * @return array
     */
    private function storeBossWithdrawButton()
    {
        return $this->customButton('StoreBossWithdraw');
    }

    /**
     * 通过收入类型获取对应开启的提现方式按钮组
     *
     * @param string $incomeType
     *
     * @return array
     */
    private function customButton($incomeType)
    {
        if (!$this->incomeCustomStatus($incomeType)) {
            return $this->defaultButton();
        }
        return $this->_customButton($incomeType);
    }

    /**
     * @param string $incomeType
     *
     * @return array
     */
    private function _customButton($incomeType)
    {
        $defaultButton = ['service_switch' => $this->serviceSwitch()];

        foreach ($this->incomeCustomSet($incomeType) as $item) {
            if (in_array($item, static::$payWay)) {
                $defaultButton[$item] = [
                    'name'  => $this->buttonName($item),
                    'value' => $item
                ];
            }
        }
        return $defaultButton;
    }

    /**
     * 提现方式按钮，读取提现设置中开启的提现方式，返回按钮格式
     *
     * @return array
     */
    private function defaultButton()
    {
        $defaultButton = ['service_switch' => $this->serviceSwitch()];

        foreach ($this->withdrawSet() as $key => $item) {
            if (in_array($key, static::$payWay) && $item) {
                $defaultButton[$key] = [
                    'name'  => $this->buttonName($key),
                    'value' => $key
                ];
            }
        }
        return $defaultButton;
    }

    private function buttonName($key)
    {
        $balance = Setting::get('shop.shop');

        $set = \Setting::get('shop.lang.zh_cn.income');

        $name = '';
        if ($set['name_of_withdrawal']) {
            $name = $set['name_of_withdrawal'];
        } else {
            $name = '提现';
        }
        switch ($key) {
            case 'balance':
                return $name . '到' . $balance['credit'] ?: '余额';
            case 'wechat':
                return $name . '到微信';
            case 'alipay':
                return $name . '到支付宝';
            case 'manual':
                return $name . '手动打款';
            case 'huanxun':
                return $name . '到银行卡';
            case 'eup_pay':
                return $name . '到EUP';
            case 'yop_pay':
                return $name . '到易宝';
            case 'converge_pay':
                return $name . '到银行卡-HJ';
            default:
                return '';
        }
    }

    /**
     * 收入类型是否开启自定义提现方式
     *
     * @param string $incomeType
     *
     * @return bool
     */
    private function incomeCustomStatus($incomeType)
    {
        return !!Setting::get("withdraw.{$incomeType}.withdraw_type");
    }

    /**
     * 收入类型自定义提现方式设置
     *
     * @param string $incomeType
     *
     * @return array
     */
    private function incomeCustomSet($incomeType)
    {
        return Setting::get("withdraw.{$incomeType}.withdraw_method");
    }

    /**
     * 提现设置
     *
     * @return array
     */
    private function withdrawSet()
    {
        return Setting::get('withdraw.income');
    }

    /**
     * 是否显示劳务税
     *
     * @return int
     */
    private function serviceSwitch()
    {
        return Setting::get('withdraw.income.service_switch') ?: 0;
    }
}
