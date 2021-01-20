<?php

namespace app\frontend\modules\coupon\services\models\TimeLimit;

use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
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
//        if ($this->receiveDays() > $this->dbCoupon->time_days) {
//            return false;
//        }

        $coupon = $this->coupon->getMemberCoupon();
        if(Carbon::createFromTimestamp(strtotime($coupon->time_end))->endOfDay()->lessThan(Carbon::now())){
            return false;
        }

//        if($this->dbCoupon->time_end->endOfDay()->lessThan(Carbon::now())){
//            return false;
//        }


        return true;
    }

    private function receiveDays()
    {
        return $this->coupon->getMemberCoupon()->get_time->diffInDays();
    }

    public function expiredTime(){



        //dd($this->dbCoupon->get_time );
        $time_end_time = $this->coupon->getMemberCoupon()->get_time->toArray();
       // dd($time_end_time);
        if($this->dbCoupon->time_days == 0){
            $days = 9999;
        }else{
            $days = $this->dbCoupon->time_days;
        }

        $d = date("Y-m-d", $time_end_time["timestamp"]);
        return strtotime("{$d} +{$days} day");



    }
}