<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/2
 * Time: 下午4:18
 */

namespace app\common\models;


use app\backend\models\BackendModel;
use app\backend\modules\member\models\MemberRecord;
use app\common\events\member\MemberChangeRelationEvent;
use app\common\events\member\MemberCreateRelationEvent;
use app\common\events\member\RegisterByAgent;
use app\common\observers\member\MemberObserver;
use app\frontend\modules\member\models\SubMemberModel;
use app\Jobs\ModifyRelationJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yunshop\Commission\models\Agents;
use Yunshop\Hotel\common\models\Hotel;
use Yunshop\Supplier\common\models\Supplier;
use Yunshop\TeamDividend\models\TeamDividendAgencyModel;

/**
 * Class MemberShopInfo
 * @package app\common\models
 *
 * @property int m_id
 * @property int member_id
 * @property int uniacid
 * @property int parent_id
 * @property int group_id
 * @property int level_id
 * @property int inviter
 * @property int is_black
 * @property string province_name
 * @property string city_name
 * @property string area_name
 * @property int province
 * @property int city
 * @property int area
 * @property string address
 * @property string referralsn
 * @property int is_agent
 * @property string alipayname
 * @property string alipay
 * @property string content
 * @property int status
 * @property int child_time
 * @property int agent_time
 * @property int apply_time
 * @property string relation
 * @property int created_at
 * @property int updated_at
 * @property int deleted_at
 * @property string custom_value
 * @property int validity
 * @property int member_form
 * @property string pay_password
 * @property string salt
 * @property string withdraw_mobile
 * @property string wechat
 * @property string yz_openid
 * @property string invite_code
 *
 * @property MemberLevel level
 *
 * @method static self search($search = [])
 */
class MemberShopInfo extends BaseModel
{
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'yz_member';

    protected $guarded = [''];

    public $primaryKey = 'member_id';

    public static function boot()
    {
        parent::boot();
        static::observe(new MemberObserver());

        static::addGlobalScope('uniacid', function (Builder $builder) {
            return $builder->uniacid();
        });
    }

    public function level()
    {
        return $this->hasOne('app\common\models\MemberLevel', 'id', 'level_id');
    }

    /**
     * @param static $query
     * @param array $search
     */
    public function scopeSearch($query, $search)
    {
        if ($search['level_id']) $query->where('level_id', $search['level_id']);

        if ($search['group_id']) $query->where('group_id', $search['group_id']);;

        //todo 移除 member_level、member_group 检索，规范使用
        if ($search['member_level']) $query->where('level_id', $search['member_level']);
        if ($search['member_group']) $query->where('group_id', $search['member_group']);;
    }


    /**
     * 检索关联会员等级表
     * @param $query
     * @return mixed
     */
    public function scopeWithLevel($query)
    {
        return $query->with([
            'level' => function ($query) {
                return $query;
            }
        ]);
    }


    /**
     * 获取用户信息
     *
     * @param $memberId
     * @return mixed
     */
    public static function getMemberShopInfo($memberId)
    {
        // 为了方便解决重复查询当前用户的bug
        if (!is_null(Member::current()->yzMember) && (Member::current()->yzMember->member_id == $memberId)) {
            return Member::current()->yzMember;
        }
        return self::select('*')->where('member_id', $memberId)
            ->uniacid()
            ->first(1);
    }

    /**
     * 通过 openid 获取用户信息
     * @param $openid
     * @return mixed
     */
    public static function getMemberShopInfoByOpenid($openid)
    {
        return static::uniacid()->whereHas('hasOneMappingFans', function ($query) use ($openid) {
            $query->where('openid', '=', $openid);
        })->first();
    }

    /**
     * 获取我的下线
     *
     * @return mixed
     */
    public static function getAgentCount()
    {
        return self::uniacid()
            ->where('parent_id', \YunShop::app()->getMemberId())
            ->where('is_black', 0)
            ->count();
    }

    /**
     * 获取指定推荐人的下线
     *
     * @param $uids
     * @return mixed
     */
    public static function getAgentAllCount($uids)
    {
        return self::selectRaw('parent_id, count(member_id) as total')
            ->uniacid()
            ->whereIn('parent_id', $uids)
            ->where('is_black', 0)
            ->groupBy('parent_id')
            ->get();
    }

    public function hasManySelf()
    {
        return $this->hasMany('app\common\models\MemberShopInfo', 'parent_id', 'member_id');
    }

    public function hasOnePreSelf()
    {
        return $this->hasOne('app\common\models\MemberShopInfo', 'member_id', 'parent_id');
    }

    public function hasOneMappingFans()
    {
        return $this->hasOne('app\common\models\McMappingFans', 'uid', 'member_id');
    }

    /**
     * 用户是否为黑名单用户
     *
     * @param $member_id
     * @return bool
     */
    public static function isBlack($member_id)
    {
        $member_model = self::getMemberShopInfo($member_id);

        if (1 == $member_model->is_black) {
            return true;
        } else {
            return false;
        }
    }

    public static function getUserInfo($mobile)
    {
        return self::uniacid()
            ->where('withdraw_mobile', $mobile)
            ->first();
    }

    /**
     * 获取该公众号下所有用户的 member ID
     *
     * @return mixed
     */
    public static function getYzMembersId()
    {
        return static::uniacid()
            ->select(['member_id'])
            ->get();
    }

    public static function getSubLevelMember($uid, $pos)
    {
        return self::uniacid()
            ->select(['member_id', 'parent_id', 'relation'])
            ->whereRaw('FIND_IN_SET(?,relation) = ?', [$uid, $pos])
            ->get();
    }

    //新增关联订单表
    public function hasOneOrder()
    {
        return $this->hasOne(Order::class, 'uid', 'member_id');
    }

    //主表yz_member,从表mc_member
    public function hasOneMember()
    {
        return $this->hasOne(Member::class, 'uid', 'member_id');
    }

    //关联供应商
    public function hasOneSupplier()
    {
        return $this->hasOne(Supplier::class, 'member_id', 'member_id');
    }

    //关联门店
    public function hasOneStore()
    {
        return $this->hasOne(Store::class, 'uid', 'member_id');
    }

    //关联酒店
    public function hasOneHotel()
    {
        return $this->hasOne(Hotel::class, 'uid', 'member_id');
    }

    public static function chkInviteCode($code)
    {
        return self::select('member_id')->where('invite_code', $code)
            ->uniacid()
            ->count();
    }

    public static function updateInviteCode($member_id, $code)
    {
        return self::uniacid()
            ->where('member_id', $member_id)
            ->update(['invite_code' => $code]);
    }

    public static function getMemberIdForInviteCode($code)
    {
        return self::uniacid()
            ->where('invite_code', $code)
            ->pluck('member_id');
    }

    public static function change_relation($uid, $parent_id)
    {
        if (is_numeric($parent_id)) {
            if (!empty($parent_id)) {
                $parent = SubMemberModel::getMemberShopInfo($parent_id);

                $parent_is_agent = !empty($parent) && $parent->is_agent == 1 && $parent->status == 2;

                if (!$parent_is_agent) {
                    return ['status' => -1];
                }
            }

            $member_relation   = Member::setMemberRelation($uid, $parent_id);
            $plugin_commission = app('plugins')->isEnabled('commission');
            $plugin_team       = app('plugins')->isEnabled('team-dividend');

            if (isset($member_relation) && $member_relation !== false) {
                $member = MemberShopInfo::getMemberShopInfo($uid);

                $record            = new MemberRecord();
                $record->uniacid   = \YunShop::app()->uniacid;
                $record->uid       = $uid;
                $record->parent_id = $member->parent_id;

                $record->save();

                $rs = event(new MemberChangeRelationEvent($uid, $parent_id));

                \Log::debug('----change relation----', [$uid, $parent_id, $rs]);

                foreach ($rs as $item) {
                    if (is_null($item)) {
                        continue;
                    }

                    if (1 == $item['status']) {
                        $member->parent_id = $parent_id;
                        $member->inviter   = 1;

                        $member->save();

                        if ($plugin_team) {
                            $team = TeamDividendAgencyModel::getAgentByUidId($uid)->first();

                            if (!is_null($team)) {
                                $team->parent_id = $parent_id;
                                $team->relation  = $member->relation;

                                $team->save();
                            }
                        }

                        if ($plugin_commission) {
                            $agents = Agents::uniacid()->where('member_id', $uid)->first();

                            if (!is_null($agents)) {
                                $agents->parent_id = $parent_id;
                                $agents->parent    = $member->relation;

                                $agents->save();
                            }

                            $agent_data = [
                                'member_id' => $uid,
                                'parent_id' => $parent_id,
                                'parent'    => $member->relation
                            ];

                            event(new RegisterByAgent($agent_data));
                        }

                        //更新2、3级会员上线和分销关系
                        dispatch(new ModifyRelationJob($uid, $member_relation, $plugin_commission));

                        return ['status' => 1];
                    }
                }

            }

            return ['status' => 0];
        }
    }

    /**
     * 查询会员邀请码
     *
     * @return mixed
     */
    public function getInviteCode($inviteCode)
    {
        $data = self::select('member_id')->where('invite_code', $inviteCode)
            ->uniacid()
            ->count();

        if ($data > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 查询邀请码会员
     *
     * @return mixed
     */
    public function getInviteCodeMember($inviteCode)
    {
        $member = self::select('member_id')
            ->where('invite_code', $inviteCode)
            ->with([
                'hasOneMember' => function ($q) {
                    $q->select('uid', 'nickname', 'avatar', 'realname');
                }
            ])
            ->uniacid()
            ->first();

        if ($member) {
            return $member;
        } else {
            return false;
        }
    }

    /**
     * 获取父id
     *
     * @param $member_id
     * @return int  parent_id
     */
    public function getParentId($member_id)
    {
        return self::uniacid()->where('member_id', $member_id)->value('parent_id');
    }


    /**
     * 会员ID检索
     * @param $query
     * @param $memberId
     * @return mixed
     */
    public function scopeOfMemberId($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

}
