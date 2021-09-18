<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/4/12
 * Time: 14:35
 */

namespace app\frontend\modules\cart\manager;

use app\frontend\modules\cart\group\CartGroupManager;
use app\frontend\modules\cart\group\DefaultShopGroup;
use app\frontend\modules\cart\models\CartGoods;
use app\frontend\modules\cart\models\MemberCart;
use app\frontend\modules\cart\models\ShopCart;
use Illuminate\Container\Container;

class CartContainer extends Container
{
    public function __construct()
    {
        $this->bindModels();

        //$this->setting = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order');
    }

    private function bindModels()
    {

        $this->bind('ShopCart', function ($cartManager, $attributes) {
            return new ShopCart($attributes);
        });


        $this->bind('MemberCart', function ($cartManager, $attributes) {
            return new MemberCart($attributes);
        });

        $this->bind('CartGoods', function ($cartManager, $attributes) {
            return new CartGoods($attributes);
        });

        /**
         * 购物车分组模型
         */
        $this->singleton('CartGroupManager', function ($deductionSettingManager) {
            return new CartGroupManager();
        });

        /**
         * 购物车商品模型
         */
        $this->singleton('CartGoodsManager', function ($deductionSettingManager) {
            return new CartGoodsManager();
        });

    }
}