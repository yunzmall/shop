<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/15
 * Time: 15:46
 */

namespace app\frontend\modules\cart\manager;


use app\common\models\GoodsOption;

class GoodsOptionAdapter extends GoodsAdapter
{


    public function getPrice()
    {
        return $this->currentCalculator()->product_price;
    }

    public function getMarketPrice()
    {
        return $this->currentCalculator()->market_price;
    }

    public function getCostPrice()
    {
        return $this->currentCalculator()->cost_price;
    }

    /**
     * @return GoodsOption
     */
    public function currentCalculator()
    {
        return $this->cart->goodsOption;
    }
}