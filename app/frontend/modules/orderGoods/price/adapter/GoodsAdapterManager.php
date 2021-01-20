<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/4
 * Time: 9:56
 */

namespace app\frontend\modules\orderGoods\price\adapter;


use app\common\modules\orderGoods\models\PreOrderGoods;

class GoodsAdapterManager
{
    protected $priceClass;


    public function __construct()
    {

    }

    static public function preOrderGoods(PreOrderGoods $preOrderGoods)
    {
        if ($preOrderGoods->isOption()) {
            $priceCalculator = new GoodsOptionPriceAdapter($preOrderGoods->goodsOption);

        } else {
            $priceCalculator = new GoodsPriceAdapter($preOrderGoods->goods);
        }

        return $priceCalculator;
    }

}