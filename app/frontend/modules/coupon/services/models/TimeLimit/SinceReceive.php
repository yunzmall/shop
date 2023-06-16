<?php

namespace app\frontend\modules\coupon\services\models\TimeLimit;

use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/29
 * Time: 下午5:17
 */
class SinceReceive extends TimeLimit
{
    public function valid()
    {
        if ($this->dbCoupon->time_days == false) {
            return true;
        }
        if ($this->coupon->getMemberCoupon()->get_time->addDays($this->dbCoupon->time_days)->lessThan(Carbon::now())) {
            return false;
        }
        return true;
    }

    private function receiveDays()
    {
        return $this->coupon->getMemberCoupon()->get_time->diffInDays();
    }

    public function expiredTime()
    {
        $time_end_time = $this->coupon->getMemberCoupon()->get_time->timestamp;
        if ($this->dbCoupon->time_days == 0) {
            $days = 9999;
        } else {
            $days = $this->dbCoupon->time_days;
        }
        $d = date("Y-m-d", $time_end_time);
        return strtotime("{$d} +{$days} day");
    }
}