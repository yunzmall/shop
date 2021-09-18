<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/7/6
 * Time: 14:40
 */

namespace app\frontend\modules\payment\listeners;


use app\common\events\payment\RechargeComplatedEvent;

class WechatH5Recharge
{
    /**
     * @param RechargeComplatedEvent $event
     * @return null
     */
    public function onGetPaymentTypes(RechargeComplatedEvent $event)
    {

        if (\Setting::get('shop.pay.weixin') &&
            \Setting::get('shop.pay.wechat_h5') &&
            \YunShop::request()->type != 2 &&
            \YunShop::request()->type != 7
        ) {

            $result = [
                'name' => '微信H5',
                'value' => '50',
                'need_password' => '0'

            ];
            $event->addData($result);

        }
        return null;
    }


    /**
     * @param RechargeComplatedEvent $event
     */
    public function subscribe($events)
    {
        $events->listen(
            RechargeComplatedEvent::class,
            self::class . '@onGetPaymentTypes'
        );
    }
}