<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/9
 * Time: 15:22
 */

namespace app\backend\modules\order\steps;



use app\common\services\steps\interfaces\ElementSteps;

class OrderStatusStepManager
{
    public $order;

    protected $items;

    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * 获取当前订单配置
     * @return array|mixed
     */
    public function getOrderStepSetting()
    {
       // $configs = \app\common\modules\shop\ShopConfig::current()->get('');

        $configs = $this->getBasicStep();

        return $configs;
    }

    protected function getBasicStep()
    {
        return [
            \app\backend\modules\order\steps\Create::class,
            \app\backend\modules\order\steps\Pay::class,
            \app\backend\modules\order\steps\Send::class,
            \app\backend\modules\order\steps\Receive::class,
            \app\backend\modules\order\steps\Cancel::class,
            ];
    }

    public function getStepItems()
    {
        $stepItems =  $this->_stepItems()->sortBy(function (OrderStepFactory $step) {
            return $step->sort();
        })->map(function (OrderStepFactory $step) {
            return [
                'title' => $step->getTitle(),
                'desc' => $step->getDescription(),
                'status' => $step->getStatus(),
                'icon' => $step->getIcon(),
            ];
        })->values()->toArray();

        return $stepItems;
    }

    /**
     * @return mixed
     */
    public function _stepItems()
    {

        if (isset($this->items)) {
            return $this->items;
        }

        $this->items =  collect($this->getOrderStepSetting())->map(function ($step) {
            if (class_exists($step)) {
                return new $step($this->order);
            }
            return null;
        })->filter(function (ElementSteps $step) {
            //开启的
            return isset($step) && $step->isShow();
        });

        return $this->items;
    }
}