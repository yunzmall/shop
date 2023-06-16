<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/5/25
 * Time: 18:44
 */

namespace app\frontend\modules\cart\deduction;

use app\frontend\modules\cart\deduction\models\PreCartGoodsDeduction;
use app\frontend\modules\cart\models\CartGoods;

abstract class BaseCartDeduction
{
    /**
     * @var CartGoods
     */
    protected $cartGoods;
    /**
     * 优惠名
     * @var string
     */
    protected $name;
    /**
     * 优惠码
     * @var
     */
    protected $code;
    /**
     * @var float
     */
    private $amount;


    protected $weight = 0;

    public function __construct(CartGoods $cartGoods)
    {
        $this->cartGoods = $cartGoods;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    public function getWeight()
    {
        return $this->weight;

    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取总金额
     * @return float|int
     */
    public function getAmount()
    {
        if (isset($this->amount)) {
            return $this->amount;
        }

        $this->amount = $this->_getAmount();
        if ($this->amount) {
            $preModel = new PreCartGoodsDeduction([
                'code' => $this->code,
                'amount' => $this->amount ?: 0,
                'name' => $this->getName(),
            ]);
            $preModel->setCartGoods($this->cartGoods);
        }


        return $this->amount ?: 0;
    }


    /**
     * @return float
     */
    abstract protected function _getAmount();

}