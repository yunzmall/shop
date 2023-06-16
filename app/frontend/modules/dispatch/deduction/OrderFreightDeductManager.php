<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/4/13
 * Time: 18:18
 */

namespace app\frontend\modules\dispatch\deduction;


use app\frontend\modules\deduction\models\Deduction;
use app\frontend\modules\deduction\OrderGoodsDeductionCollection;
use app\frontend\modules\order\models\PreOrder;

class OrderFreightDeductManager
{
    /**
     * @var PreOrder
     */
    private $order;



    private $orderFreightDeductionCollection;

    public function __construct(PreOrder $order)
    {
        $this->order = $order;
    }

    /**
     * @return OrderGoodsDeductionCollection
     */
    public function getOrderFreightDeductions()
    {
        if (!isset($this->orderFreightDeductionCollection)) {
            $this->orderFreightDeductionCollection = $this->_getOrderFreightDeduction();
        }
        return $this->orderFreightDeductionCollection;
    }


    /**
     * 获取并订单抵扣项并载入到订单模型中
     */
    private function _getOrderFreightDeduction()
    {

        $orderDeductions = $this->getEnableDeductions()->map(function (Deduction $deduction) {

            $orderDeduction = new PreOrderFreightDeductionCalculation();

            $orderDeduction->init($deduction, $this->order->getFreightManager());
            return $orderDeduction;
        })->values();
//        ->filter(function (PreOrderFreightDeduction $freightDeduction) {
//        return $freightDeduction->isEnableDeductFreight();
//    })
        return $orderDeductions;
    }


    /**
     * 开启的抵扣项
     * @return \app\framework\Database\Eloquent\Collection
     */
    private function getEnableDeductions()
    {
        //由于获取开启抵扣都是相同的所以这里把这部分代码提取出来
        return \app\frontend\modules\deduction\EnableDeductionService::getInstance()->getEnableDeductions($this->order);

    }

}