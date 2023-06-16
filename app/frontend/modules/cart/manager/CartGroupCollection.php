<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/4/30
 * Time: 18:09
 */

namespace app\frontend\modules\cart\manager;

use app\frontend\modules\cart\models\ShopCart;
use Illuminate\Support\Collection;

class CartGroupCollection extends Collection
{
    /**
     * 第一个选中的购物车店铺类型
     * 设置商品是否可选择
     */
    public function firstCheckedCart()
    {
        $firstShop = $this->first(function (ShopCart $shop) {
            return $shop->isCheckedCartGoods();
        });

        //根据商铺类型判断是否能其他商品是否能选择
        $this->map(function (ShopCart $shop) use($firstShop) {
            $shop->setDisable($firstShop);
            return $shop;
        });
    }


    public function getCartInvalidGoods()
    {
        $invalidCart = $this->map(function (ShopCart $shop) {
            return $shop->getFailureCart();
        })->collapse()->map(function ($cartGoods) {
            return $cartGoods->memberCart;
        })->values();


        return $invalidCart;
    }


    /**
     * 获取指定购物车折扣
     * @param null $code
     * @return mixed
     */
    public function getSingleCartDiscount($code = null)
    {

        return $this->sum(function (ShopCart $shop) use ($code) {
            if (is_null($code)) {
                return $shop->cartDiscounts()->sum('amount');
            }
            return $shop->cartDiscounts()->where('code', $code)->sum('amount');
        });
    }

    public function getSingleCartDeductions($code = null)
    {

        return $this->sum(function (ShopCart $shop) use ($code) {
            if (is_null($code)) {
                return $shop->cartDeductions()->sum('amount');
            }
            return $shop->cartDeductions()->where('code', $code)->sum('amount');
        });
    }

    public function getSingleCartExtraCharges($code = null)
    {

        return $this->sum(function (ShopCart $shop) use ($code) {
            if (is_null($code)) {
                return $shop->cartExtraCharges()->sum('amount');
            }
            return $shop->cartExtraCharges()->where('code', $code)->sum('amount');
        });
    }

    public function getCartDiscounts()
    {
        // 将所有订单商品的优惠
        return $this->reduce(function (Collection $result, ShopCart $cart) {
            return $result->merge($cart->cartDiscounts());
        },collect());
    }


    public function getCartDeductions()
    {
        // 将所有订单商品的优惠
        return $this->reduce(function (Collection $result, ShopCart $cart) {
            return $result->merge($cart->cartDeductions());
        },collect());
    }

    public function getCartExtraCharges()
    {
        // 将所有订单商品的优惠
        return $this->reduce(function (Collection $result, ShopCart $cart) {
            return $result->merge($cart->cartExtraCharges());
        },collect());
    }
}