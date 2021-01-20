<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/8/21
 * Time: 15:04
 */

namespace app\frontend\modules\coupon\listeners;


use app\common\models\Coupon;
use app\common\models\MemberCoupon;
use app\common\models\UniAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CouponExpired
{
    public function subscribe()
    {
        \Event::listen('cron.collectJobs', function () {
            \Cron::add('Coupon-expired', '*/10 * * * *', function () {
                $this->handle();
                return;
            });
        });
    }

    public function handle()
    {
        \Log::info('优惠券过期');
        set_time_limit(0);
        $uniAccount = UniAccount::getEnable();
        foreach ($uniAccount as $u) {
            \YunShop::app()->uniacid = $u->uniacid;
            \Setting::$uniqueAccountId = $u->uniacid;

            $this->expired();
        }
    }


    public function expired()
    {
        $coupons = Coupon::get();
        $time = time();
        foreach ($coupons as $coupon) {
            //使用时间限制  日期
            if ($coupon['time_limit'] == Coupon::COUPON_DATE_TIME_RANGE ){
                if(bcsub(strtotime($coupon['time_end']),$time)<259200)
                {
                    MemberCoupon::where('coupon_id', $coupon['id'])->where('is_expired', 0)->update(['near_expiration' => 1]);
                }
                if($time > strtotime($coupon['time_end']))
                {
                       MemberCoupon::where('coupon_id', $coupon['id'])->where('is_expired', 0)->update(['is_expired' => 1]);
                }
            }
            if ($coupon['time_limit'] == Coupon::COUPON_SINCE_RECEIVE && ($coupon['time_days'] !== 0)) {
                MemberCoupon::where('coupon_id', $coupon['id'])
                    ->where('uid','<>',0)
                    ->where(DB::raw('ifnull(`get_time`, 0) + ('.$coupon['time_days'].' * 86400) - '.$time), '<=', 259200)
                    ->where(['is_expired'=> 0 ,'near_expiration' => 0])
                    ->update(['near_expiration' => 1]);
                MemberCoupon::where('coupon_id', $coupon['id'])
                    ->where('uid','<>',0)
                    ->where(DB::raw('ifnull(`get_time`, 0) + ('.$coupon['time_days'].' * 86400)'), '<=', $time)
                    ->where('is_expired', 0)
                    ->update(['is_expired' => 1]);
          }
        }
    }
}