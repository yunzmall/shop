<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/10
 * Time: 16:22
 */

namespace app\backend\modules\refund\services;


use app\backend\modules\refund\services\button\RefundButtonBase;
use app\common\models\refund\RefundApply;

class BackendRefundButtonService
{
    protected  $refund;

    public function __construct(RefundApply $refund)
    {
        $this->refund = $refund;
    }

    public function getButtonModels()
    {
        $operationsSettings = $this->getGroupBy();
        $operations = array_map(function ($operationName) {
            /**
             * @var RefundButtonBase $operation
             */
            $operation = new $operationName($this->refund);
            if (!$operation->enable()) {
                return null;
            }
            $result['name'] = $operation->getName();
            $result['value'] = $operation->getValue();
            $result['api'] = $operation->getApi();
            $result['type'] = $operation->getType();
            $result['desc'] = $operation->getDesc();

            return $result;
        }, $operationsSettings);

        $operations = array_filter($operations);

//        dd($operations);

        return array_values($operations) ?: [];
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
                \app\backend\modules\refund\services\button\Reject::class,
                \app\backend\modules\refund\services\button\Pay::class,
                \app\backend\modules\refund\services\button\Consensus::class,
            ],
            RefundApply::REFUND_TYPE_RETURN_GOODS => [
                \app\backend\modules\refund\services\button\Reject::class,
                \app\backend\modules\refund\services\button\Pass::class,
                \app\backend\modules\refund\services\button\Pay::class,
                \app\backend\modules\refund\services\button\Consensus::class,
            ],
            RefundApply::REFUND_TYPE_EXCHANGE_GOODS => [
                \app\backend\modules\refund\services\button\Reject::class,
                \app\backend\modules\refund\services\button\Pass::class,
                \app\backend\modules\refund\services\button\Resend::class,
                \app\backend\modules\refund\services\button\Close::class,
            ],
        ];
    }


    protected function getAllOperationButton()
    {
        $button = collect([
            [
                'name' => '同意退款',
                'value' => 1,
                'api' => 'refund.pay',
                'type' => '',
            ],
            [
                'name' => '手动退款',
                'value' => 2,
                'api' => 'refund.operation.consensus',
                'type' => '',
            ],
            [
                'name' => '通过申请(需客户寄回商品)',
                'value' => 3,
                'api' => 'refund.operation.pass',
                'type' => '',
            ],
            [
                'name' => '确认发货',
                'value' => 5,
                'api' => 'refund.operation.resend',
                'type' => '',
            ],
            [
                'name' => '关闭申请(换货完成)',
                'value' => 10,
                'api' => 'refund.operation.close',
                'type' => '',
            ],
            [
                'name' => '驳回申请',
                'value' => -1,
                'api' => 'refund.operation.reject',
                'type' => '',
            ],
        ]);

        return $button;
    }

}