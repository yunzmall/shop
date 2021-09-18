<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/14
 * Time: 15:50
 */

namespace app\frontend\modules\cart\manager;


use Illuminate\Support\Collection;
use app\frontend\modules\cart\models\CartGoods;

class CartGoodsCollection extends Collection
{
    /**
     * 每个购物车商品注入店铺模型
     * @param $shop
     */
    public function setShop($shop) {

        foreach ($this as $goods) {
            $goods->setShop($shop);
        }
    }

    public function cartValidate()
    {
        $this->map(function (CartGoods $goods) {
            return $goods->goodsValidate();
        });
    }

    /**
     * 获取商品总价
     * @return int
     */
    public function getPrice()
    {
        return $this->sum(function (CartGoods $goods) {
            return $goods->getPrice();
        });
    }

    public function setCartDisable($isDisable)
    {
        $this->map(function (CartGoods $goods) use ($isDisable) {
            return $goods->setDisable($isDisable);
        });
    }

    public function getEstimatedPrice()
    {
        return $this->sum(function (CartGoods $goods) {
            return $goods->getEstimatedPrice();
        });
    }

    public function isCheckedCartGoods()
    {
        return $this->contains(function (CartGoods $goods) {
            return $goods->isChecked();
        });
    }

    public function getCartGoodsDiscounts()
    {
        // 将所有订单商品的优惠
         return $this->reduce(function (Collection $result, CartGoods $goods) {
            return $result->merge($goods->getCartGoodsDiscounts());
        },collect());
    }

    public function getCartGoodsExtraCharges()
    {
        return $this->reduce(function (Collection $result, CartGoods $goods) {
            return $result->merge($goods->getCartGoodsExtraCharges());
        },collect());
    }

    public function getCartGoodsDeductions()
    {
        return $this->reduce(function (Collection $result, CartGoods $goods) {
            return $result->merge($goods->getCartGoodsDeductions());
        },collect());
    }
}