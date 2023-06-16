<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/21
 * Time: 10:28
 */

namespace app\frontend\modules\finance\deduction\deductionSettings;

use app\frontend\modules\deduction\DeductionSettingInterface;

class BalanceShopDeductionSetting implements DeductionSettingInterface
{
    public function getWeight()
    {
        return 50;
    }

    // todo 将运费抵扣分离出去
    public function isEnableDeductDispatchPrice()
    {
        return \Setting::get('finance.balance.balance_deduct_freight')?true:false;
    }

    public function isMaxDisable()
    {
        return !\Setting::get('finance.balance.balance_deduct');

    }

    public function isMinDisable()
    {
        return true;
    }

    /**
     * 不抵扣运费
     * @return bool
     */
    public function isDispatchDisable()
    {
        return true;
    }

    public function getMaxFixedAmount()
    {
        return false;
    }

    public function getMaxPriceProportion()
    {
        return \Setting::get('finance.balance.money_max')?:false;
    }


    public function getMaxDeductionType()
    {
        return 'GoodsPriceProportion';
    }

    public function getDeductionAmountType()
    {
        return 0;
    }

    public function getMinDeductionType()
    {
        return false;
    }

    public function getMinFixedAmount()
    {
        return false;
    }

    public function getMinPriceProportion()
    {
        return \Setting::get('finance.balance.money_min')?:false;
    }


    public function getAffectDeductionAmount()
    {
        return false;
    }
}