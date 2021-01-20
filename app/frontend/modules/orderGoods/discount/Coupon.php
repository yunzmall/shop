<?php


namespace app\frontend\modules\orderGoods\discount;


class Coupon extends BaseDiscount
{
    protected $code = 'coupon';
    protected $name = '优惠券优惠';

    protected function _getAmount()
    {
        // todo 订单优惠券计算需要参照抵扣的结构重构, 这里先调用一次订单的抵扣金额,来保证先绑定订单商品优惠券的模型,后通过模型获取订单商品优惠券总金额
        $this->orderGoods->order->getPriceAfter('coupon');

        return max($this->orderGoods->getCouponAmount(), 0);
        //return max($this->orderGoods->getPriceBefore($this->code) - $this->orderGoods->getCouponAmount(), 0);
    }

}