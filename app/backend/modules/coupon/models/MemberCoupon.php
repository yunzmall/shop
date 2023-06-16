<?php

namespace app\backend\modules\coupon\models;

use app\common\models\coupon\CouponUseLog;
use app\common\models\coupon\ShoppingShareCouponLog;
use app\common\models\MemberCoupon as BaseMemberCoupon;

class MemberCoupon extends BaseMemberCoupon
{

    public function self()
    {
        return $this->hasMany(BaseMemberCoupon::class, 'uid', 'uid');
    }

    public function couponLog()
    {
        return $this->hasMany(CouponLog::class, 'member_id', 'uid');
    }

    public function shareCouponLog()
    {
        return $this->hasMany(ShoppingShareCouponLog::class, 'receive_uid', 'uid');
    }

    public function couponUseLog()
    {
        return $this->hasMany(CouponUseLog::class, 'member_id', 'uid');
    }

}
