<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/10/13
 * Time: 下午5:05
 */

namespace app\frontend\modules\deduction\orderGoods\amount;

/**
 * 按比例抵扣金额
 * Class Proportion
 * @package app\frontend\modules\deduction\orderGoods\amount
 */
class GoodsPriceProportion extends OrderGoodsDeductionAmount
{
    /**
     * @return float|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getMaxAmount()
    {
        $result = $this->getGoodsDeduction()->getMaxPriceProportion() * $this->getBaseAmount('Deduction') / 100;

        return max($result, 0);
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getMinAmount()
    {
        $result = $this->getGoodsDeduction()->getMinPriceProportion() * $this->getBaseAmount('Deduction') / 100;

        return max($result, 0);
    }

    /**
     * @param $key
     */
    private function getBaseAmount($key)
    {
        $type = $this->getGoodsDeduction()->getDeductionAmountType() ?: 0;
        switch ($type) {
            case 1:
                $amount = $this->orderGoods->getPriceBeforeWeight($this->getGoodsDeduction()->getCode() . $key) - $this->orderGoods->goods_cost_price;
                break;
            default :
                $amount = $this->orderGoods->getPriceBeforeWeight($this->getGoodsDeduction()->getCode() . $key);
                break;
        }
        return max($amount, 0);
    }

    public function hasMinAmount(){
        return $this->getGoodsDeduction()->getMinPriceProportion() > 0;
    }
}