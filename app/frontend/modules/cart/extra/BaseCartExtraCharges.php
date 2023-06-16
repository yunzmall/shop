<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/5/26
 * Time: 9:30
 */

namespace app\frontend\modules\cart\extra;

use app\frontend\modules\cart\extra\models\PreCartGoodsExtraCharges;
use app\frontend\modules\cart\models\CartGoods;

abstract class BaseCartExtraCharges
{
    /**
     * @var CartGoods
     */
    protected $cartGoods;
    /**
     * 费用名称
     * @var string
     */
    protected $name;
    /**
     * 费用码
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
            $preModel = new PreCartGoodsExtraCharges([
                'code' => $this->code,
                'amount' => $this->amount ?: 0,
                'name' => $this->name,
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

