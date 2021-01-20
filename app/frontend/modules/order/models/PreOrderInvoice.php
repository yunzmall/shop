<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/24
 * Time: 17:12
 */

namespace app\frontend\modules\order\models;


use app\common\models\order\OrderInvoice;

class PreOrderInvoice extends OrderInvoice
{

    protected $order;


    protected function _initAttributes()
    {
        $attributes = [
//            'invoice_type' => $this->order->getRequest()->input('invoice_type', null),//发票类型
            'email' =>  $this->order->getRequest()->input('email'),//电子邮箱
//            'rise_type' =>  $this->order->getRequest()->input('rise_type', null),//收件人或单位
            'collect_name' =>  $this->order->getRequest()->input('call', ''),//抬头或单位名称
            'company_number' =>  $this->order->getRequest()->input('company_number', ''),//单位识别号
            'uniacid'       => \Yunshop::app()->uniacid
        ];
        if ($this->order->getRequest()->input('invoice_type')){
            $attributes['invoice_type'] = $this->order->getRequest()->input('invoice_type');
        }
        if ($this->order->getRequest()->input('rise_type')){
            $attributes['rise_type'] = $this->order->getRequest()->input('rise_type');
        }

        $attributes = array_merge($this->getAttributes(), $attributes);
        $this->setRawAttributes($attributes);
    }

    /**
     * @param PreOrder $order
     */
    public function setOrder(PreOrder $order)
    {
        $this->order = $order;

        $this->_initAttributes();

        $this->order->setRelation('orderInvoice', $this);

    }
}