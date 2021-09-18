<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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