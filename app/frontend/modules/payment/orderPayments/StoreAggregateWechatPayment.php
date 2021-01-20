<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/10/27
 * Time: 17:26
 */

namespace app\frontend\modules\payment\orderPayments;

use app\frontend\modules\payment\orderPayments\WebPayment;

class StoreAggregateWechatPayment extends WebPayment
{
    public function canUse()
    {
        return parent::canUse() && $this->isOpen();
    }

    public function isOpen()
    {
        $is_open = \Setting::get('plugin.store_aggregate_pay.is_open');

        return  \YunShop::plugin()->get('store-aggregate-pay') && $is_open != '1';
    }
}