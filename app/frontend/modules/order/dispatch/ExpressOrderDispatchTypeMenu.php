<?php


namespace app\frontend\modules\order\dispatch;



class ExpressOrderDispatchTypeMenu extends DispatchTypeMenu
{
    public function enable()
    {

        //虚拟订单不支持快递方式
        return !$this->order->isVirtual() && parent::enable();
    }

    public function getId()
    {
        return 1;
    }
}