<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/23
 * Time: 11:32
 */

namespace app\backend\modules\order\services\type;


use app\frontend\modules\order\operations\OrderOperationInterface;

trait BackendButtonTrait
{

    //订单操作
    protected function getButtonModels()
    {
        $operationsSettings = $this->getCurrentOperations();
        $operations = array_map(function ($operationName) {
            /**
             * @var OrderOperationInterface $operation
             */
            $operation = new $operationName($this->getOrder(), $this);
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

    //加载插件按钮到商店订单列表
    protected function getPluginOperations($status, $arr = [])
    {
        $plugin_arr = empty(\app\common\modules\shop\ShopConfig::current()->get('backend_order_list_plugin_button.' . $status)) ? [] : \app\common\modules\shop\ShopConfig::current()->get('backend_order_list_plugin_button.' . $status);
        $arr = array_merge($arr, $plugin_arr);
        if ($plugin_arr) {
            foreach ($plugin_arr as $v) {
                $class = new $v($this->getOrder(), $this);
                if (!method_exists($class, 'replace')) {
                    continue;
                }
                if (!$replace_class = $class->replace()) {
                    continue;
                }
                $key = array_search($replace_class, $arr);
                if ($key !== false) {
                    unset($arr[$key]);
                }
            }
        }
        return array_values($arr);
    }

    //根据订单状态获取当前操作按钮
    protected function getCurrentOperations()
    {
        $method_name = $this->getStatusMethod($this->getOrder()->status);

        return $this->$method_name();
    }

    //0 待支付
    protected function waitPayOperations()
    {
        return $this->getPluginOperations(0, [
            \app\backend\modules\order\operations\Pay::class,
        ]);
    }

    //1 待发货
    protected function waitSendOperations()
    {
        return $this->getPluginOperations(1, [
            \app\backend\modules\order\operations\Send::class,
            \app\backend\modules\order\operations\SeparateSend::class,
        ]);
    }

    //2 待收货
    protected function waitReceiveOperations()
    {
        return $this->getPluginOperations(2, [
            \app\backend\modules\order\operations\SeparateSend::class,
            \app\backend\modules\order\operations\Receive::class,
            \app\backend\modules\order\operations\CancelSend::class,
        ]);
    }

    //3 已完成
    protected function completeOperations()
    {
        return $this->getPluginOperations(3, []);
    }

    // -1 已关闭
    protected function closeOperations()
    {
        return $this->getPluginOperations(-1, []);
    }

    /**
     * 根据状态返回方法
     * @param $status
     * @return mixed
     */
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
}