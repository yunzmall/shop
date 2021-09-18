<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/6/15
 * Time: 11:02
 */

namespace app\frontend\modules\finance\deduction\deductionSettings;

use app\frontend\modules\deduction\DeductionSettingInterface;

class BalanceGoodsDeductionSetting implements DeductionSettingInterface
{
    //这个开关是获取商品设置的值是回去统一设置的

    public function getWeight()
    {
        return 10;
    }

    /**
     * @var \app\frontend\models\goods\Sale
     */
    private $setting;

    function __construct($goods)
    {
        $this->setting = $goods->hasOneSale;

    }

    // todo 将运费抵扣分离出去
    public function isEnableDeductDispatchPrice()
    {
        return false;
    }

    public function isMaxDisable()
    {
        return !\Setting::get('finance.balance.balance_deduct') || empty($this->setting->balance_deduct);

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
        return !\Setting::get('finance.balance.balance_deduct');
    }

    public function getMaxFixedAmount()
    {
        return false;
    }

    /**
     *
     * @return mixed
     */
    public function getMaxPriceProportion()
    {
        return \Setting::get('finance.balance.money_max')?:false;
    }

    public function getDeductionAmountType()
    {
        return 0;
    }

    public function getMinDeductionType()
    {
        return 'GoodsPriceProportion';
    }

    public function getMinFixedAmount()
    {
        return false;
    }

    public function getMinPriceProportion()
    {
        return \Setting::get('finance.balance.money_min')?:false;
    }

    public function getMaxDeductionType()
    {
        return 'GoodsPriceProportion';
    }
}
