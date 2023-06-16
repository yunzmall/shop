<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/5/25
 * Time: 18:39
 */

namespace app\frontend\modules\cart\node;


use app\frontend\modules\cart\deduction\BaseCartDeduction;
use app\frontend\modules\cart\models\CartGoods;
use app\frontend\modules\order\PriceNode;

class CartGoodsDeductionsPriceNode extends PriceNode
{
    protected $cartGoods;

    private $deduction;

    public function __construct(CartGoods $cartGoods,BaseCartDeduction $deduction, $weight)
    {
        $this->cartGoods = $cartGoods;

        $this->deduction = $deduction;

        parent::__construct($weight);
    }

    public function getKey()
    {
        return $this->deduction->getCode();
    }

    /**
     * @return float|int|mixed
     * @throws \app\common\exceptions\AppException
     */
    public function getPrice()
    {
        if ($this->cartGoods->isChecked()) {
            return $this->cartGoods->getPriceBefore($this->getKey()) - $this->deduction->getAmount();
        } else {
            return $this->cartGoods->getPriceBefore($this->getKey());
        }

       // return $this->cartGoods->getPriceBefore($this->getKey()) - $this->deduction->getAmount();
    }
}