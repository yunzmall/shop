<?php
namespace app\frontend\modules\coupon\listeners;

use app\common\facades\Setting;
use app\common\models\Coupon;
use app\common\models\GoodsCouponQueue;
use app\common\models\UniAccount;
use app\common\services\SystemMsgService;
use app\Jobs\addSendCouponJob;
use app\Jobs\addSendCouponLogJob;
use app\Jobs\updateCouponQueueJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use app\backend\modules\coupon\services\MessageNotice;
use app\common\models\MemberCoupon;

class CouponSysMessage
{
    use DispatchesJobs;
    public $uniacid;

    public function handle()
    {
        \Log::info('优惠券后台系统消息');
        set_time_limit(0);
        $uniAccount = UniAccount::getEnable();
        foreach ($uniAccount as $u) {
            \YunShop::app()->uniacid = $u->uniacid;
            Setting::$uniqueAccountId = $u->uniacid;
            $this->uniacid = $u->uniacid;
            (new SystemMsgService())->couponNotice();
        }
    }

    public function subscribe()
    {
        \Event::listen('cron.collectJobs', function () {
            \Cron::add('coupon-sys-msg', '0 2 * * *', function () {
                $this->handle();
                return;
            });
        });
    }
}