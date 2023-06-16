<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/14
 * Time: 20:12
 */

namespace app\common\models\member;


use app\common\models\BaseModel;

class MemberInviteGoodsLog extends BaseModel
{
    public $table = 'yz_member_goods_invite_log';
    public $guarded = [''];
    public $timestamps = true;

    public static function getLogByMemberId($member_id)
    {
        return self::uniacid()->where('member_id', $member_id)->first();
    }
}