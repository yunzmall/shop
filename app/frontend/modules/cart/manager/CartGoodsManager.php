<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/4/26
 * Time: 11:10
 */

namespace app\frontend\modules\cart\manager;

use app\common\models\Goods;
use app\frontend\modules\cart\models\CartGoods;
use app\frontend\modules\cart\models\MemberCart;
use Illuminate\Container\Container;

class CartGoodsManager extends Container
{
    public function __construct()
    {
//        $this->bind('shop', function (CartGoodsManager $cartGoodsManager, array $params) {
//            return new CartGoods();
//        });
    }

    /**
     * @param Goods $goods
     * @return CartGoods
     */
    public function getCartGoods($goods)
    {

        foreach ($this->getBindings() as $key => $value) {
            $cartGoods = $this->make($key);
            $cartGoods->setRelation('goods', $goods);
            if ($cartGoods->verify($goods)) {

                return $cartGoods;
            }
        }
        $cartGoods = app('CartContainer')->make('CartGoods');
        $cartGoods->setRelation('goods', $goods);
        return $cartGoods;
    }
}