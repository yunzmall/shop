<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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