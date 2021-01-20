<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/1
 * Time: 11:24
 */

namespace app\common\modules\order;

use app\common\models\Order;
use app\frontend\modules\order\operations\OrderOperation;
use app\frontend\modules\order\operations\OrderOperationInterface;


class BackendOrderOperationsCollector
{
    /**
     * @param Order $order
     * @return array
     * @throws \app\common\exceptions\AppException
     */
    public function getOperations(Order $order)
    {
        $operationsSettings = $this->getOperationsSetting($order);
        $operations = array_map(function ($operationName) use ($order) {
            /**
             * @var OrderOperationInterface $operation
             */
            $operation = new $operationName($order);
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

    protected function getOperationsSetting($order)
    {

        $settings =  array_merge($this->getBasicOperations(), $this->addOperationConfig());

        return $settings[$this->getStatusCode($order)];
    }

    public function addOperationConfig()
    {
       return [];
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

    /**
     * @param Order $order
     */
    public function getAllOperations(Order $order)
    {

    }

    protected function getStatusCode(Order $order)
    {
        $defaults = [0 => 'waitPay', 1 => 'waitSend', 2 => 'waitReceive', 3 => 'complete', -1 => 'close'];

        return $defaults[$order->status];
    }
}