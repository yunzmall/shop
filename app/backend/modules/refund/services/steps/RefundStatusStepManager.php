<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/14
 * Time: 16:59
 */

namespace app\backend\modules\refund\services\steps;


use app\common\models\refund\RefundApply;
use app\common\services\steps\interfaces\ElementSteps;

class RefundStatusStepManager
{
    public $refund;

    protected $items;

    public function __construct($refundApply)
    {
        $this->refund = $refundApply;
    }

    /**
     * 获取当前订单配置
     * @return array|mixed
     */
    public function getRefundStepSetting()
    {
        // $configs = \app\common\modules\shop\ShopConfig::current()->get('');

        $configs = $this->getGroupBy();

        return $configs;
    }

    //根据退款申请类型返回按钮
    protected function getGroupBy()
    {
        return $this->buttonGroupBy()[$this->refund->refund_type];
    }

    protected function buttonGroupBy()
    {
        return [
            RefundApply::REFUND_TYPE_REFUND_MONEY => [
                \app\backend\modules\refund\services\steps\Create::class,
                \app\backend\modules\refund\services\steps\Agree::class,
            ],
            RefundApply::REFUND_TYPE_RETURN_GOODS => [
                \app\backend\modules\refund\services\steps\Create::class,
                \app\backend\modules\refund\services\steps\Agree::class,
                \app\backend\modules\refund\services\steps\UserReturn::class,
            ],
            RefundApply::REFUND_TYPE_EXCHANGE_GOODS => [
                \app\backend\modules\refund\services\steps\Create::class,
                \app\backend\modules\refund\services\steps\Agree::class,
                \app\backend\modules\refund\services\steps\UserReturn::class,
                \app\backend\modules\refund\services\steps\Resend::class,
            ],
        ];
    }

    public function getStepItems()
    {
        $stepItems =  $this->_stepItems()->sortBy(function (ElementSteps $step) {
            return $step->sort();
        })->map(function (ElementSteps $step) {
            return [
                'title' => $step->getTitle(),
                'desc' => $step->getDescription(),
                'status' => $step->getStatus(),
                'icon' => $step->getIcon(),
                'value' => $step->getValue(),
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

        $this->items =  collect($this->getRefundStepSetting())->map(function ($step) {
            if (class_exists($step)) {
                return new $step($this->refund);
            }
            return null;
        })->filter(function (ElementSteps $step) {
            //开启的
            return isset($step) && $step->isShow();
        });

        return $this->items;
    }
}