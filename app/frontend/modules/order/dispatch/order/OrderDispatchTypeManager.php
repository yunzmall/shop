<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/3
 * Time: 9:51
 */

namespace app\frontend\modules\order\dispatch\order;


use app\common\exceptions\AppException;
use app\common\models\DispatchType;
use app\frontend\modules\order\models\PreOrder;

class OrderDispatchTypeManager
{
    /**
     * @var PreOrder
     */
    private $order;


    public function __construct(PreOrder $order)
    {
        $this->order = $order;
    }

    /**
     * 当前订单配送方式
     */
    public function getDispatchType()
    {
        return DispatchType::where('id', $this->order->dispatch_type_id)->first();

    }

    /**
     * 获取配送方法处理配置类
     * @return array|mixed
     */
    public function getDispatchTypesSetting()
    {
        $configs = \app\common\modules\shop\ShopConfig::current()->get('shop-foundation.order-dispatch-save');


        return $configs;
    }

    /**
     * 返回订单配送方法
     * @return mixed
     */
    public function getOrderDispatchTypeClass()
    {

        $dispatchType = $this->getDispatchType();

        if (is_null($dispatchType)) {
            return null;
        }
        $orderDispatchType = null;
        foreach ($this->getDispatchTypesSetting()  as $code => $dispatchTypeClass) {
            if ($dispatchType->code == $code && class_exists($dispatchTypeClass)) {
                $orderDispatchType = new $dispatchTypeClass($this->order, $dispatchType);
                break;
            }
        }
        return $orderDispatchType;
    }
}