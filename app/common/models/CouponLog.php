<?php

namespace app\common\models;

use app\common\models\BaseModel;

class CouponLog extends BaseModel
{
    public $table = 'yz_coupon_log';
    public $guarded = [];
    public $timestamps = false;

    public $appends = ['getfrom_name'];

    const SEND = 0;
    const RECEIVE = 1;
    const SHOP_GIVE = 4;
    const MEMBER_SUBGIFT = 5;
    const SIGN_REWARD = 6;
    const ROOM_WATCH_REWARD = 7;
    const ROOM_MRMBER_REWARD = 8;
    const ROOM_ANCHOR_REWARD = 9;
    const MEMBER_MERGE = 10;

    //多对一关系
    public function coupon()
    {
        return $this->belongsTo('app\common\models\Coupon', 'couponid', 'id');
    }

    //多对一关系
    public function member()
    {
        return $this->belongsTo('app\common\models\Member', 'member_id', 'uid');
    }

    public function getGetfromNameAttribute()
    {
        return $this->getTypeNames()[$this->getfrom]?:'';
    }

    public function getTypeNames()
    {
        return [
            self::SEND => '发放',
            self::RECEIVE => '领取',
            self::SHOP_GIVE => '购物赠送',
            self::MEMBER_SUBGIFT => '会员转赠',
            self::SIGN_REWARD => '签到奖励',
            self::ROOM_WATCH_REWARD => '直播会员观看奖励',
            self::ROOM_MRMBER_REWARD => '直播间会员奖励',
            self::ROOM_ANCHOR_REWARD => '直播主播奖励',
            self::MEMBER_MERGE => '会员合并转入',
        ];
    }
}