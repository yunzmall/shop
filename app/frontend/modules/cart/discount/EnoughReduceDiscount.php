<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/4/29
 * Time: 14:07
 */

namespace app\frontend\modules\cart\discount;


use app\frontend\modules\cart\models\CartGoods;

class EnoughReduceDiscount extends BaseCartDiscount
{
    protected $code = 'enoughReduce';
    protected $name = '全场满减优惠';

    /**
     * @return float|int
     * @throws \app\common\exceptions\AppException
     */
    protected function _getAmount()
    {
        if ($this->getShopGoodsPrice() == 0) {
            return 0;
        }
        // (订单商品成交金额/订单中同种商品总成交金额 ) * 订单单品满减金额
        return ($this->cartGoods->getPriceBefore($this->getCode()) / $this->getShopGoodsPrice()) * $this->enoughReduce();
    }

    /**
     * @return int|mixed
     * @throws \app\common\exceptions\AppException
     */
    protected function enoughReduce()
    {

        //只有商城订单参加
        if($this->cartGoods->goods()->plugin_id != 0) {
            return 0;
        }


        $shopDiscountAmount =  $this->cartGoods->getShop()->getDiscountAmount($this->getShopGoodsPrice());

        return $shopDiscountAmount;
        //return $this->cartGoods->shop->getDiscount()->getAmountByCode($this->code)->getAmount();
    }

    /**
     * 店铺中能够进行全场满减优惠的商品的价格总和
     * @return float
     */
    protected function getShopGoodsPrice()
    {
        return $this->cartGoods->shop->carts->sum(function (CartGoods $preCartGoods) {
            if ($preCartGoods->isChecked() && $preCartGoods->goods()->plugin_id == 0) {
                return $preCartGoods->getPriceBefore($this->getCode());

            }
            return 0;
//            return $preCartGoods->getPriceBefore($this->getCode());
        });
    }
}