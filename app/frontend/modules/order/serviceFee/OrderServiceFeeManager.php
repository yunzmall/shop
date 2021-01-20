<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/20
 * Time: 16:55
 */

namespace app\frontend\modules\order\serviceFee;


use app\framework\Database\Eloquent\Collection;
use app\frontend\models\order\PreOrderServiceFee;
use app\frontend\modules\order\models\PreOrder;

class OrderServiceFeeManager
{
    public $orderServiceFee;
    /**
     * @var PreOrder
     */
    protected $order;

    /**
     * OrderFee constructor.
     * @param PreOrder $order
     */
    public function __construct(PreOrder $order)
    {
        $this->order = $order;

        // 订单服务费集合
        $order->setRelation('orderServiceFees', new Collection());

    }

    public function getOrderServiceFees()
    {
        if (!isset($this->orderServiceFee)) {

            $orderServiceFees = $this->getEnableServiceFee()->map(function (BaseOrderServiceFee $serviceFee) {
                /**
                 * @var PreOrderServiceFee $orderServiceFee
                 */
                $orderServiceFee = new PreOrderServiceFee();

                $orderServiceFee->init($serviceFee, $this->order);
                return $orderServiceFee;
            });
            $this->orderServiceFee = $orderServiceFees;
        }

        return $this->orderServiceFee;
    }

    public function getEnableServiceFee()
    {
        $orderServiceFee = collect();


        $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-service-fee');

        foreach ($configs as $configItem) {
            $class = call_user_func($configItem['class'], $this->order);
            if ($class->enable()) {
                $orderServiceFee->put($configItem['key'], $class);
            }
        }
        return $orderServiceFee;
    }

    public function getAmount()
    {
        return $this->getOrderServiceFees()->sum(function (PreOrderServiceFee $orderServiceFee) {
            if ($orderServiceFee->isChecked()) {
                // 每一种服务费
                return $orderServiceFee->getAmount();
            }
            return 0;
        });
    }
}