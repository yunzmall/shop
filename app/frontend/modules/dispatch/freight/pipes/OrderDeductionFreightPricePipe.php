<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/4/25
 * Time: 19:06
 */

namespace app\frontend\modules\dispatch\freight\pipes;


use app\frontend\models\order\PreOrderDeduction;
use app\frontend\modules\dispatch\deduction\PreOrderFreightDeduction;
use app\frontend\modules\dispatch\models\OrderFreight;

class OrderDeductionFreightPricePipe extends PricePipe
{
    private $deduction;


    public function __construct(OrderFreight $orderFreight,PreOrderFreightDeduction $deduction, $weight = 5000)
    {
        $this->deduction = $deduction;
        parent::__construct($orderFreight, $weight);
    }


    public function getKey()
    {
        return $this->deduction->getCode().'Deduction';
    }

    public function getAmount()
    {
        return $this->deduction->getAmount()->getMoney();
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    function getPrice()
    {

        if ($this->deduction->getOrderDeduction()->isChecked() && $this->deduction->isEnableDeductFreight()) {
//            dump($this->getKey(), $this->orderFreight->getPriceBefore($this->getKey()), $this->getAmount());

            $a = max($this->orderFreight->getPriceBefore($this->getKey()) - $this->getAmount(), 0);
//            dump($a, $this->getKey(),$this->orderFreight->priceCache, $this->orderFreight->getOrder()->priceCache);
            return $a;
        } else {
            return $this->orderFreight->getPriceBefore($this->getKey());
        }
    }
}