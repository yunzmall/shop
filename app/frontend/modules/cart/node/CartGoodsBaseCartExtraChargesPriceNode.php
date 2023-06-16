<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/5/26
 * Time: 9:25
 */

namespace app\frontend\modules\cart\node;


use app\frontend\modules\cart\extra\BaseCartExtraCharges;
use app\frontend\modules\cart\models\CartGoods;
use app\frontend\modules\order\PriceNode;

class CartGoodsBaseCartExtraChargesPriceNode extends PriceNode
{
    protected $cartGoods;

    private $extraCharges;

    public function __construct(CartGoods $cartGoods,BaseCartExtraCharges $extraCharges, $weight)
    {
        $this->cartGoods = $cartGoods;

        $this->extraCharges = $extraCharges;

        parent::__construct($weight);
    }

    public function getKey()
    {
        return $this->extraCharges->getCode();
    }

    /**
     * @return float|int|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getPrice()
    {
        if ($this->cartGoods->isChecked()) {
            return $this->cartGoods->getPriceBefore($this->getKey()) + $this->extraCharges->getAmount();
        } else {
            return $this->cartGoods->getPriceBefore($this->getKey());
        }

        //return $this->cartGoods->getPriceBefore($this->getKey()) + $this->extraCharges->getAmount();
    }
}