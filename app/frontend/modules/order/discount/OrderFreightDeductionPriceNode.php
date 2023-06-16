<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/4/26
 * Time: 10:44
 */

namespace app\frontend\modules\order\discount;

use app\common\exceptions\AppException;
use app\frontend\models\order\PreOrderDeduction;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\order\PriceNode;

class OrderFreightDeductionPriceNode  extends PriceNode
{
    private $preOrderDeduction;
    private $order;

    public function __construct(PreOrder $order, PreOrderDeduction $preOrderDeduction, $weight)
    {
        $this->order = $order;
        $this->preOrderDeduction = $preOrderDeduction;
        parent::__construct($weight);
    }

    function getKey()
    {
        return $this->preOrderDeduction->getCode() . 'FreightDeduction';
    }

    /**
     * @return mixed
     * @throws \app\common\exceptions\AppException
     */
    function getPrice()
    {

        if ($this->preOrderDeduction->isChecked() && $this->preOrderDeduction->openFreightDeduction()) {
            return max($this->order->getPriceBefore($this->getKey()) - $this->preOrderDeduction->getUsableFreightDeduction()->getMoney(),0);
        } else {
            return $this->order->getPriceBefore($this->getKey());
        }
    }
}