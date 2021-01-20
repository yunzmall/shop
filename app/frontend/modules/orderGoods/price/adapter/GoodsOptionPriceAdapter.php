<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/3
 * Time: 15:28
 */

namespace app\frontend\modules\orderGoods\price\adapter;


class GoodsOptionPriceAdapter extends BaseGoodsPriceAdapter
{
    public function getPrice()
    {
        return $this->goods()->product_price;
    }

    public function getCostPrice()
    {
        return $this->goods()->cost_price;
    }

    public function getMarketPrice()
    {
        return $this->goods()->market_price;
    }

    protected function getDefaultDealPrice()
    {
        $level_discount_set = \Setting::get('discount.all_set');

        switch ($level_discount_set['type']) {
            case 1:
                $deal_price = $this->goods()->market_price;
                break;
            default:
                $deal_price = $this->goods()->product_price;
        }

        return $deal_price;
    }

    protected function _getDealPrice()
    {
        return $this->goods()->deal_Price;
    }


    protected function goods()
    {
        return $this->goods;
    }
}