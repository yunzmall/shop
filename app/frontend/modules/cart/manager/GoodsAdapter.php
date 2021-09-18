<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/14
 * Time: 13:59
 */

namespace app\frontend\modules\cart\manager;


use app\common\models\Goods;

class GoodsAdapter
{

    protected $cart;

    public function __construct($cart)
    {
        $this->cart = $cart;
    }


    public function getPrice()
    {
        return $this->currentCalculator()->price;
    }

    public function getCostPrice()
    {
        return $this->currentCalculator()->cost_price;
    }

    /**
     * 市场价
     * @return mixed
     */
    public function getMarketPrice()
    {
        return $this->currentCalculator()->market_price;
    }

    protected $vipDiscountAmount;

    protected $vipDiscountLog;

    /**
     * @return float
     * @throws \app\common\exceptions\AppException
     */
    public function _getVipDiscountAmount()
    {
        if (!isset($this->vipDiscountAmount)) {
            $this->vipDiscountAmount = $this->goods()->getVipDiscountAmount($this->currentCalculator()->getGoodsPriceAdapter());
            $this->vipDiscountLog = $this->goods()->vipDiscountLog;
        }
        return $this->vipDiscountAmount;
    }

    /**
     * @return Goods
     */
    public function currentCalculator()
    {
        return $this->cart->goods;
    }

    /**
     * @return Goods
     */
    public function goods()
    {
        return $this->cart->goods;
    }
}