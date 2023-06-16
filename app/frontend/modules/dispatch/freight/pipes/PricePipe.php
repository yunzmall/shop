<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/13
 * Time: 10:09
 */

namespace app\frontend\modules\dispatch\freight\pipes;


use app\frontend\modules\dispatch\models\OrderFreight;
use app\frontend\modules\order\PriceNode;

abstract class PricePipe extends PriceNode
{
    protected $weight;

    /**
     * @var OrderFreight
     */
    protected $orderFreight;

    public function __construct(OrderFreight $orderFreight, $weight)
    {
        $this->orderFreight = $orderFreight;

        parent::__construct($weight);
    }

    abstract public function getAmount();

}