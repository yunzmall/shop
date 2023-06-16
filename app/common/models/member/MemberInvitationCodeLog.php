<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2018/12/29
 * Time: 11:40
 */

namespace app\common\models\member;


use app\common\models\BaseModel;
use app\framework\Database\Eloquent\Builder;
use app\common\models\MemberShopInfo;

class MemberInvitationCodeLog extends BaseModel
{
    public $table = 'yz_member_invitation_log';
    public $search_fields = ['member_id', 'invitation_code', 'mid'];
    public $guarded = [''];

    public function getDates()
    {
        return ['created_at'];
    }

    public static function searchLog($params)
    {
        $res = self::select(['id', 'invitation_code', 'created_at', 'member_id', 'mid'])->uniacid();

        if ($params['code']) {
            $res->where('invitation_code', trim($params['code']));
        }
        if (!empty($params['times']['start']) && !empty($params['times']['end'])) {
            $res->where('created_at', '>=', $params['times']['start'])->where('created_at', '<=', $params['times']['end']);
        }

        if ($params['mid'] && $params['mid'] > 0) {
            $res->where('mid', $params['mid'])
                ->orWhere('member_id', $params['mid']);
        }

        $res = $res->with([
            'yzMember' => function ($query) {
                $query->select(['inviter', 'member_id'])
                    ->with([
                        'hasOneMember' => function ($query) {
                            $query->select(['uid', 'avatar', 'nickname']);
                        }
                    ]);
            }
        ])
            ->with([
                'hasOneMcMember' => function ($q) {
                    $q->select(['member_id', 'inviter'])
                        ->with([
                        'hasOneMember' => function ($q) {
                            $q->select(['uid', 'avatar', 'nickname']);
                        }
                    ]);
                }
            ]);

        return $res;
    }

    public static function getLogByMemberId($member_id)
    {
        return self::uniacid()->where('member_id', $member_id)->first();
    }

    //使用邀请码用户id
    public function yzMember()
    {
        return $this->hasOne('\app\common\models\MemberShopInfo', 'member_id', 'member_id');
    }

    //推荐用户id
    public function hasOneMcMember()
    {
        return $this->hasOne('\app\common\models\MemberShopInfo', 'member_id', 'mid');
    }
}