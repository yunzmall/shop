<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021-08-02
 * Time: 11:02
 */

namespace app\common\models\member;


use app\common\models\BaseModel;
use app\common\models\Member;

class MemberCancel extends BaseModel
{
    public $table = 'yz_member_cancel_record';
    public $guarded = [''];
    public $timestamps = true;

    public static function getByUid($uid)
    {
        return self::uniacid()->where(['member_id'=>$uid,'status'=>1])->first();
    }

    public function hasOneMember()
    {
        return $this->hasOne(Member::class, 'uid', 'member_id');
    }

    public static function search($search)
    {
        $model = self::uniacid()->with(['hasOneMember'=>function($q){
            $q->select(['uid','realname','nickname','mobile','avatar']);
        }]);
        if (!empty($search['member_id'])) {
            $model->where('member_id', $search['member_id']);
        }
        if (!empty($search['member'])) {
            $model->join('mc_members', function ($join) use ($search) {
                $join->on('yz_member_cancel_record.member_id', 'mc_members.uid')
                    ->where('mc_members.realname', 'like', '%'.$search['member'].'%')
                    ->orWhere('mc_members.nickname', 'like', '%'.$search['member'].'%')
                    ->orWhere('mc_members.mobile', 'like', '%'.$search['member'].'%');
            });
        }
        if (!empty($search['status'])) {
            $model->where('status', intval($search['status']));
        }
        if (!empty($search['create_time'])) {
            $model->whereBetween('created_at', $search['create_time']);
        }
        return $model;
    }
}