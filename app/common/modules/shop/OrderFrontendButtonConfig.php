<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/8/2
 * Time: 10:28
 */

namespace app\common\modules\shop;


class OrderFrontendButtonConfig extends CommonConfig
{
    protected function _getItems()
    {
        $result = [
            'member_order_operations' => [
                'waitPay' => [
                    \app\frontend\modules\order\operations\member\Pay::class,
                    \app\frontend\modules\order\operations\member\Close::class,
                ],
                'waitSend' => [
                    \app\frontend\modules\order\operations\member\ApplyRefund::class,
                    \app\frontend\modules\order\operations\member\ContactCustomerService::class,
                    \app\frontend\modules\order\operations\member\Refunding::class,
                    \app\frontend\modules\order\operations\member\Refunded::class,
                    \app\frontend\modules\order\operations\member\Coupon::class, //分享优惠卷
                    \app\frontend\modules\order\operations\member\ExpeditingDelivery::class, //催发货
                ],
                'waitReceive' => [
                    \app\frontend\modules\order\operations\member\ExpressInfo::class,
                    \app\frontend\modules\order\operations\member\Receive::class,
                    \app\frontend\modules\order\operations\member\ApplyRefund::class,
                    \app\frontend\modules\order\operations\member\ContactCustomerService::class,
                    \app\frontend\modules\order\operations\member\Refunding::class,
                    \app\frontend\modules\order\operations\member\Refunded::class,
                    \app\frontend\modules\order\operations\member\Coupon::class, //分享优惠卷
                ],
                'complete' => [
                    \app\frontend\modules\order\operations\member\ExpressInfo::class,
                    \app\frontend\modules\order\operations\member\Delete::class,
                    \app\frontend\modules\order\operations\member\ApplyRefund::class,
                    \app\frontend\modules\order\operations\member\ContactCustomerService::class,
                    \app\frontend\modules\order\operations\member\Refunding::class,
                    \app\frontend\modules\order\operations\member\Refunded::class,
                    \app\frontend\modules\order\operations\member\CheckInvoice::class,
                    \app\frontend\modules\order\operations\member\Coupon::class, //分享优惠卷
                    \app\frontend\modules\order\operations\member\ViewEquity::class, //查看卡券
                ],
                'close' => [
                    \app\frontend\modules\order\operations\member\ExpressInfo::class,
                    \app\frontend\modules\order\operations\member\Delete::class,
                    \app\frontend\modules\order\operations\member\Refunded::class,
                ],
            ],
            'order_frontend_button' => [//插件按钮配置
                ['plugin_id' => 0,'class' => \app\frontend\modules\order\services\OrderFrontendButton::class,'weight'=>0],
            ],
            'replace_order_frontend_button' => [//需替换原按钮的配置
                //例：['search'=>'\app\frontend\modules\order\operations\member\ExpressInfo::class','replace'=>'\plugin\blind-box\order\operations\member\ExpressInfo::class']
                'waitPay' => [],
                'waitSend' => [],
                'waitReceive' => [],
                'complete' => [],
                'close' => [],
            ],
        ];
        $this->items = $result;  //先为items赋值，不然下边会死循环
        $plugins = app('plugins')->getEnabledPlugins('*');
        foreach ($plugins as $plugin) {
            $class = $plugin->app();
            if (method_exists($class,'orderButtonConfig')) {
                $class->orderButtonConfig();
            }
        }
        return $this->items;
    }
}