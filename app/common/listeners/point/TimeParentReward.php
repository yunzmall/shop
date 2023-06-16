<?php
/**
 * Created by PhpStorm.
 * User: CGOD
 * Date: 2019/12/18
 * Time: 16:00
 */

namespace app\common\listeners\point;

use app\common\services\point\ParentReward;
use app\frontend\modules\coupon\services\CronSendService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use app\common\facades\Setting;
use app\common\models\UniAccount;

//商品购买每月赠送优惠券
class TimeParentReward
{
    use DispatchesJobs;

    public function handle()
    {
        set_time_limit(0);
        $uniAccount = UniAccount::get() ?: [];
        foreach ($uniAccount as $u) {
            Setting::$uniqueAccountId = \YunShop::app()->uniacid = $u->uniacid;
            (new ParentReward())->award();
        }
    }

    public function subscribe()
    {
        \Event::listen('cron.collectJobs', function () {
            \Cron::add('MonthParentRewardPoint', '*/13 * * * *', function () {
                $this->handle();
                return;
            });
        });
    }
}