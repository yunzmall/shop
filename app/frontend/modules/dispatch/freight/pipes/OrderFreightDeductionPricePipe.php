<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/13
 * Time: 10:36
 */

namespace app\frontend\modules\dispatch\freight\pipes;


use app\frontend\modules\dispatch\deduction\BaseFreightDeduction;
use app\frontend\modules\dispatch\models\OrderFreight;

class OrderFreightDeductionPricePipe extends PricePipe
{
    private $deduction;


    public function __construct(OrderFreight $orderFreight,BaseFreightDeduction $deduction, $weight = 5000)
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
        return $this->deduction->getAmount();
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    function getPrice()
    {
        if ($this->deduction->isChecked()) {
            return max($this->orderFreight->getPriceBefore($this->getKey()) - $this->getAmount(),0);
        } else {
            return $this->orderFreight->getPriceBefore($this->getKey());
        }
    }
}