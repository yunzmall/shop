<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/23
 * Time: 3:13 下午
 */

namespace app\frontend\modules\payment\paymentSettings\shop;


use Yunshop\StoreBalance\model\BalanceSet;
use Yunshop\StoreCashier\common\models\StoreSetting;

class StoreBalanceSetting extends BaseSetting
{
    public function canUse()
    {
        if (!app('plugins')->isEnabled('store-balance')) {
            return false;
        }

        if (!request()->input('store_id')) {
            return false;
        }
        $setting = BalanceSet::where('store_id', request()->input('store_id'))->first();
        if (!$setting->value['is_open']) {
            return false;
        }
        $storeSetting = StoreSetting::getStoreSettingByStoreId(request()->input('store_id'))->where('key', 'store_balance')->first();
        if (!$storeSetting->value['is_open_recharge']) {
            return false;
        }
        $storeSetting = StoreSetting::getStoreSettingByStoreId(request()->input('store_id'))->where('key', 'store')->first();
        if (!$storeSetting->value['payment_types']['store_balance_pay']) {
            return false;
        }
        return true;
    }

    public function exist()
    {
        return \Setting::get('plugin.store_balance') !== null;
    }
}