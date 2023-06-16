<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/7/27
 * Time: 17:42
 */

namespace app\frontend\modules\order\services;


use app\frontend\modules\order\operations\OrderOperationInterface;

class OrderFrontendButton extends OrderFrontendButtonBase
{
    public function enable()
    {
        return true;
    }

    public function getButton()
    {
        $button = array_map(function ($operationName){
            /**
             * @var OrderOperationInterface $operation
             */
            $operation = new $operationName($this->order);
            if (!$operation->enable()) {
                return null;
            }
            $result['name'] = $operation->getName();
            $result['value'] = $operation->getValue();
            $result['api'] = $operation->getApi();
            $result['type'] = $operation->getType();
            return $result;
        }, $this->getStatus());

        $button = array_filter($button);
        return array_values($button) ? : [];
    }

}