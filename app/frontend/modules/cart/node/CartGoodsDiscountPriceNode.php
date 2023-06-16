<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/4/29
 * Time: 16:28
 */

namespace app\frontend\modules\cart\node;


use app\frontend\modules\cart\discount\BaseCartDiscount;
use app\frontend\modules\cart\models\CartGoods;
use app\frontend\modules\order\PriceNode;

class CartGoodsDiscountPriceNode extends PriceNode
{
    private $discount;

    private $cartGoods;

    public function __construct(CartGoods $cartGoods,BaseCartDiscount $discount, $weight)
    {
        $this->cartGoods = $cartGoods;

        $this->discount = $discount;

        parent::__construct($weight);
    }

    public function getKey()
    {
        return $this->discount->getCode();
    }

    /**
     * @return float|int|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getPrice()
    {

        if ($this->cartGoods->isChecked()) {
            return $this->cartGoods->getPriceBefore($this->getKey()) - $this->discount->getAmount();
        } else {
            return $this->cartGoods->getPriceBefore($this->getKey());
        }
    }
}