<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/3
 * Time: 15:27
 */

namespace app\frontend\modules\orderGoods\price\adapter;



abstract class BaseGoodsPriceAdapter
{
    protected $goods;


    protected $dealPrice;

    public function __construct($goods)
    {
        $this->goods = $goods;
    }


    //成交价
    public function getDealPrice()
    {
        $this->dealPrice = $this->_getDealPrice();

        if (is_null($this->dealPrice)) {
            $this->dealPrice = $this->getDefaultDealPrice();
        }

        return $this->dealPrice;
    }

    abstract protected function getDefaultDealPrice();


    abstract protected function _getDealPrice();


    //商品成本价
    abstract function getCostPrice();

    //商品现价
    abstract function getPrice();

    //商品原价/市场价
    abstract function getMarketPrice();


    abstract protected function goods();
}