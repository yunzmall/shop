<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/2
 * Time: 10:28
 */

namespace app\common\modules\shop;


class OrderFrontendButtonConfig extends CommonConfig
{
    public static $current;

    static public function current()
    {
        if (!isset(static::$current)) {
            static::$current = new static();
        }
        return static::$current;
    }

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
                    \app\frontend\modules\order\operations\member\Refunding::class,
                    \app\frontend\modules\order\operations\member\Refunded::class,
                    \app\frontend\modules\order\operations\member\ExpeditingDelivery::class, //催发货
                    \app\frontend\modules\order\operations\member\ContactCustomerService::class,
                    \app\frontend\modules\order\operations\member\Coupon::class, //分享优惠卷
                ],
                'waitReceive' => [
                    \app\frontend\modules\order\operations\member\Receive::class,
                    \app\frontend\modules\order\operations\member\ExpressInfo::class,
                    \app\frontend\modules\order\operations\member\ApplyRefund::class,
                    \app\frontend\modules\order\operations\member\Refunding::class,
                    \app\frontend\modules\order\operations\member\Refunded::class,
                    \app\frontend\modules\order\operations\member\ContactCustomerService::class,
                    \app\frontend\modules\order\operations\member\Coupon::class, //分享优惠卷
                ],
                'complete' => [
                    \app\frontend\modules\order\operations\member\ApplyRefund::class,
                    \app\frontend\modules\order\operations\member\Refunding::class,
                    \app\frontend\modules\order\operations\member\Refunded::class,
                    \app\frontend\modules\order\operations\member\ExpressInfo::class,
                    \app\frontend\modules\order\operations\member\ViewEquity::class, //查看卡券
                    \app\frontend\modules\order\operations\member\CheckInvoice::class,
                    \app\frontend\modules\order\operations\member\ContactCustomerService::class,
                    \app\frontend\modules\order\operations\member\Coupon::class, //分享优惠卷
                    \app\frontend\modules\order\operations\member\Delete::class,
                ],
                'close' => [
                    \app\frontend\modules\order\operations\member\ExpressInfo::class,
                    \app\frontend\modules\order\operations\member\Refunded::class,
                    \app\frontend\modules\order\operations\member\CloseReason::class,
                    \app\frontend\modules\order\operations\member\Delete::class,
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