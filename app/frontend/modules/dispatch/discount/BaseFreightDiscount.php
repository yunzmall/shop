<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/5/23
 * Time: 下午2:21
 */

namespace app\frontend\modules\dispatch\discount;

use app\frontend\models\order\PreOrderDiscount;
use app\frontend\modules\order\models\PreOrder;
use app\frontend\modules\order\discount\BaseDiscount;

abstract class BaseFreightDiscount  extends BaseDiscount
{
    /**
     * @var \app\frontend\modules\dispatch\models\OrderFreight|mixed
     */
    protected $orderFreight;

    public function __construct(PreOrder $order)
    {
        $this->orderFreight = $order->getFreightManager();
        parent::__construct($order);
    }


    public function validate()
    {
        return true;
    }

}