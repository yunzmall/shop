<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/12
 * Time: 14:11
 */

namespace app\frontend\modules\dispatch\freight\pipes;


use app\frontend\modules\dispatch\discount\BaseFreightDiscount;
use app\frontend\modules\dispatch\models\OrderFreight;

class OrderFreightDiscountPricePipe extends PricePipe
{

    private $discount;

    public function __construct(OrderFreight $orderFreight, BaseFreightDiscount $discount, $weight)
    {
        $this->discount = $discount;
        parent::__construct($orderFreight, $weight);
    }


    public function getKey()
    {
        return $this->discount->getCode();
    }

    public function getAmount()
    {
        return $this->discount->getAmount();
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    function getPrice()
    {

        return max($this->orderFreight->getPriceBefore($this->getKey()) - $this->getAmount(),0);
    }

}