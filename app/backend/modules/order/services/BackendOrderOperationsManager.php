<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/19
 * Time: 16:27
 */

namespace app\backend\modules\order\services;

use app\backend\modules\order\common\type\OrderTypeFactory;
use app\common\models\Order;
use app\frontend\modules\order\operations\OrderOperationInterface;

class BackendOrderOperationsManager
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    protected function getOrder()
    {
        return $this->order;
    }

    /**
     * @return array
     * @throws \app\common\exceptions\AppException
     */
    public function getOperations()
    {
        $operationsSettings = $this->getCurrentOperations();
        $operations = array_map(function ($operationName) {
            /**
             * @var OrderOperationInterface $operation
             */
            $operation = new $operationName($this->getOrder());
            if (!$operation->enable()) {
                return null;
            }
            $result['name'] = $operation->getName();
            $result['value'] = $operation->getValue();
            $result['api'] = $operation->getApi();
            $result['type'] = $operation->getType();

            return $result;
        }, $operationsSettings);

        $operations = array_filter($operations);
        return array_values($operations) ?: [];
    }

    protected function getCurrentOperations()
    {
        $method_name = $this->getStatusMethod($this->getOrder()->status);

        return $this->$method_name();
    }

    protected function getOperationsSetting()
    {

        $settings = $this->getBasicOperations();


        return $settings[$this->getStatusCode($this->getOrder()->status)];
    }


    //0 待支付
    protected function waitPayOperations()
    {
        return [
            \app\backend\modules\order\operations\Pay::class,
        ];
    }

    //1 待发货
    protected function waitSendOperations()
    {
        return [
            \app\backend\modules\order\operations\Send::class,
            \app\backend\modules\order\operations\SeparateSend::class,
        ];
    }

    //2 待收货
    protected function waitReceiveOperations()
    {
        return [
            \app\backend\modules\order\operations\SeparateSend::class,
            \app\backend\modules\order\operations\Receive::class,
            \app\backend\modules\order\operations\CancelSend::class,
        ];
    }

    //3 已完成
    protected function completeOperations()
    {
        return [];
    }

    // -1 已关闭
    protected function closeOperations()
    {
        return [];
    }

    protected function getStatusMethod($status)
    {
        $methodName = [
            0 => 'waitPayOperations',
            1 => 'waitSendOperations',
            2 => 'waitReceiveOperations',
            3 => 'completeOperations',
            -1 => 'closeOperations'
        ];

        return $methodName[$status];
    }

    protected function getStatusCode($status)
    {
        $defaults = [0 => 'waitPay', 1 => 'waitSend', 2 => 'waitReceive', 3 => 'complete', -1 => 'close'];

        return $defaults[$status];
    }

    protected function getBasicOperations()
    {
        return [
            'waitPay' => [
                \app\backend\modules\order\operations\Pay::class,
            ],
            'waitSend' => [
                \app\backend\modules\order\operations\Send::class,
                \app\backend\modules\order\operations\SeparateSend::class,
            ],
            'waitReceive' => [
                \app\backend\modules\order\operations\SeparateSend::class,
                \app\backend\modules\order\operations\Receive::class,
                \app\backend\modules\order\operations\CancelSend::class,
            ],
            'complete' => [],
            'close' => [],
        ];
    }
}