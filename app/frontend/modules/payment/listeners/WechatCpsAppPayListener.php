<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/7/15
 * Time: 13:37
 */
namespace app\frontend\modules\payment\listeners;

use app\common\events\payment\GetOrderPaymentTypeEvent;
use app\common\events\payment\RechargeComplatedEvent;
use app\common\services\PayFactory;

class WechatCpsAppPayListener
{
    public function onGetPaymentTypes(GetOrderPaymentTypeEvent $event)
    {

        if (app('plugins')->isEnabled('aggregation-cps')){
            $res = \Yunshop\AggregationCps\services\WechatPayService::getWechatAppPayConfig();
            if ($res['result']){
                $result = [
                    'name' => 'CPS微信支付',
                    'value' => PayFactory::WECHAT_CPS_APP_PAY,
                    'need_password' => '0'
                ];
                $event->addData($result);
            }
        }

        return null;
    }

    public function subscribe($events)
    {
        $events->listen(
            GetOrderPaymentTypeEvent::class,
            self::class . '@onGetPaymentTypes'
        );
        $events->listen(
            RechargeComplatedEvent::class,
            self::class . '@onGetPaymentTypes'
        );
    }
}