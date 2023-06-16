<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/7/28
 * Time: 9:10
 */

namespace app\frontend\modules\order\services;

use app\common\models\Order;
use app\frontend\modules\order\operations\OrderOperationInterface;

abstract class OrderFrontendButtonBase
{
    /**
     * @var Order
     */
    protected $order;

    public function init(Order $order)
    {
        $this->order = $order;
    }

    abstract function enable();

    abstract function getButton();

    protected function getStatus()
    {
        $method = $this->__getStatus($this->order->status);
        if (empty($method) || !method_exists($this,$method)) {
            return [];
        }
        return $this->$method();
    }

    protected function __getStatus($status)
    {
        $arr = [
            -1  => 'close',
            0  => 'waitPay',
            1  => 'waitSend',
            2  => 'waitReceive',
            3  => 'complete',
        ];
        return $arr[$status];
    }

    protected function waitPay()
    {
        $arr = \app\common\modules\shop\OrderFrontendButtonConfig::current()->get('member_order_operations.waitPay');
        return $this->replaceButton($arr,'waitPay');
    }

    protected function waitSend()
    {
        $arr = \app\common\modules\shop\OrderFrontendButtonConfig::current()->get('member_order_operations.waitSend');
        return $this->replaceButton($arr,'waitSend');
    }

    protected function waitReceive()
    {
        $arr = \app\common\modules\shop\OrderFrontendButtonConfig::current()->get('member_order_operations.waitReceive');
        return $this->replaceButton($arr,'waitReceive');
    }

    protected function complete()
    {
        $arr = \app\common\modules\shop\OrderFrontendButtonConfig::current()->get('member_order_operations.complete');
        return $this->replaceButton($arr,'complete');
    }

    protected function close()
    {
        $arr = \app\common\modules\shop\OrderFrontendButtonConfig::current()->get('member_order_operations.close');
        return $this->replaceButton($arr,'close');
    }

    /**
     * 执行按钮替换
     * @param $button
     * @param $key
     * @return array
     */
    protected function replaceButton($button,$key)
    {
        $replace = \app\common\modules\shop\OrderFrontendButtonConfig::current()->get('replace_order_frontend_button.'.$key);

        foreach ($replace as $value) {
            /**
             * @var OrderOperationInterface $operation
             */
            if (!class_exists($value['replace'])) {
                continue;
            }
            $operation = new $value['replace']($this->order);
            if (method_exists($operation,'isReplace') && $operation->isReplace()) {
                //替换验证通过
                $key = array_search($value['search'],$button);
                if ($key === false) {//未找到，直接加入
                    $button[] = $value['replace'];
                } else {
                    $button[$key] = $value['replace'];
                }
            }
        }
        return $button;
    }
}