<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/10/13
 * Time: 下午5:07
 */

namespace app\frontend\modules\deduction\orderGoods\amount;

/**
 * 固定金额抵扣
 * Class FixedAmount
 * @package app\frontend\modules\deduction\orderGoods\amount
 */
class FixedAmount extends OrderGoodsDeductionAmount
{
    /**
     * @return float|mixed
     * @throws \app\common\exceptions\ShopException
     */
    public function getMaxAmount()
    {
        $result = $this->getGoodsDeduction()->getMaxFixedAmount() * $this->getOrderGoods()->total;

        //todo blank 两种抵扣同时使用问题，订单商品金额已小于抵扣金额则获取最低金额 2022/3/3
        $amount = min($result, $this->orderGoods->getPriceBefore($this->getGoodsDeduction()->getCode() . 'Deduction'));

        return max($amount, 0);
    }

    /**
     * @return float|mixed
     * @throws \app\common\exceptions\ShopException
     */
    public function getMinAmount()
    {
        $result = $this->getGoodsDeduction()->getMinFixedAmount() * $this->getOrderGoods()->total;

        //$result = min($result,$this->getOrderGoods()->getPriceBeforeWeight($this->getGoodsDeduction()->getCode().'Deduction'));

        return max($result, 0);
    }



    public function hasMinAmount(){
        return $this->getGoodsDeduction()->getMinFixedAmount() > 0;
    }
}