<?php

namespace app\common\modules\goods\dealPrice;


use app\common\facades\Setting as SettingFacades;
use app\frontend\modules\orderGoods\price\adapter\GoodsAdapterManager;

class MarketDealPrice extends BaseDealPrice
{
    public function getDealPrice()
    {
        return $this->goods->market_price;
    }

    /**
     * @return bool
     * @throws \app\common\exceptions\AppException
     */
    public function enable()
    {
        $level_discount_set = SettingFacades::get('discount.all_set');
        if (!isset($level_discount_set['type'])) {
            return false;
        }
        if ($level_discount_set['type'] != 1) {
            return false;
        }

        $priceAdapter = $this->goods->getGoodsPriceAdapter();
        $priceAdapter->setAppointPrice($this->goods->market_price);

        if (!$this->goods->memberLevelDiscount()->getAmount($priceAdapter)) {
            return false;
        }
        return true;
    }


    public function getWeight()
    {
        return 100;
    }

}