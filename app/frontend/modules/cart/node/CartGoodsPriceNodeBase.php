<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/4/29
 * Time: 14:21
 */

namespace app\frontend\modules\cart\node;


use app\frontend\modules\cart\models\CartGoods;
use app\frontend\modules\order\PriceNode;

class CartGoodsPriceNodeBase extends PriceNode
{

    protected $cartGoods;

    public function __construct(CartGoods $cartGoods, $weight)
    {
        $this->cartGoods = $cartGoods;
        parent::__construct($weight);
    }

    public function getKey()
    {
       return 'goodsPrice';
    }

    public function getPrice()
    {
        return $this->cartGoods->getPrice();
    }
}