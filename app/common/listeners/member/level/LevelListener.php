<?php
/**
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/5/25
 * Time: 下午4:54
 */

namespace app\common\listeners\member\level;


use app\common\events\balance\RechargeSuccessEvent;
use app\common\services\member\level\BalanceRechargeUpgrade;
use Illuminate\Events\Dispatcher;

class LevelListener
{
    /**
     * 会员等级升级 （监听订单）
     */
    public function subscribe(Dispatcher $event)
    {
        //付款后
        $event->listen(
            \app\common\events\order\AfterOrderPaidEvent::class,
            \app\common\services\member\level\LevelUpgradeService::class.'@checkUpgradeAfterPaid'
        );

        //完成后
        $event->listen(
            \app\common\events\order\AfterOrderReceivedEvent::class,
            \app\common\services\member\level\LevelUpgradeService::class.'@checkUpgrade'
        );

        //余额一次性充值满N元升级
        $event->listen(
            RechargeSuccessEvent::class,
            BalanceRechargeUpgrade::class.'@checkUpgrade'
        );
    }
}
