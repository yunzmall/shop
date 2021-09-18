<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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