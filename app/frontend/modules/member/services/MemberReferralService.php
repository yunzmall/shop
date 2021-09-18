<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/7/20
 * Time: 9:32
 */

namespace app\frontend\modules\member\services;


use app\backend\modules\member\models\MemberParent;
use app\common\exceptions\AppException;
use app\common\helpers\ImageHelper;
use app\common\helpers\Url;
use app\common\models\member\MemberChildren;
use app\common\models\MemberShopInfo;
use app\common\models\Order;
use app\frontend\modules\member\models\MemberModel;
use Illuminate\Support\Facades\DB;
use Yunshop\AgentListSet\admin\SetController;

class MemberReferralService
{
    public $mc_member;
    public $yz_member;
    public $member_id;
    public $member_child;

    /**
     * MemberReferralService constructor.
     * @throws AppException
     */
    public function __construct()
    {
        $this->member_id = \app\frontend\models\Member::current()->uid;
        $this->mc_member = MemberModel::getMyReferrerInfo($this->member_id)->first();
        $this->yz_member = $this->mc_member->yzMember;
//        $this->member_child = MemberChildren::where('member_id', $this->member_id)
//            ->get();
        if (!$this->mc_member) {
            throw new AppException('会员ID有误');
        }
    }

    /**
     * 我推荐的人 一二级数据v2
     * @return array
     * @throws AppException
     */
    public function getMyAgentData_v2()
    {
        $relationLevel = request()->input('relationLevel', 1);
        $pageSize = 10;
        $keyword = request()->input('keyword');
        $page = request()->page;

        //获取一级下级会员ID
        $teamMembersIds = MemberChildren::where('member_id', $this->member_id)
            ->where('level', $relationLevel)
            ->pluck('child_id')
            ->toArray();

        //todo 原先是模糊搜索，后期优化
        if (!empty($keyword)) {
            $key_uid = MemberModel::uniacid()->where('nickname', 'like', '%' . $keyword . '%')
                ->orWhere('mobile', 'like', '%' . $keyword . '%')
                ->orWhere('realname', 'like', '%' . $keyword . '%')
                ->pluck('uid')
                ->toArray();

            //获取交集
            $teamMembersIds = array_intersect($teamMembersIds, $key_uid);
        }
        //自定义分页
//        $page_start = ($page-1) * $pageSize;
//        $teamMembersIds = array_slice($teamMembersIds,$page_start,$pageSize);

        // 总订单数,总订单金额
        // todo yz_member 中的wechat 拿到外层    uid as id    withSum
        if(app('plugins')->isEnabled('agent-list-set') && \Setting::get('plugin.agent-liset-set.pay_order')){
            $teamMembers = SetController::getTeamMember($pageSize, $teamMembersIds);  //统计已完成+已支付订单
        }else{
            $teamMembers = MemberModel::select(['mobile', 'createtime', 'avatar', 'nickname', 'uid','realname'])
                ->whereIn('uid', $teamMembersIds)
                ->with(['yzMember'  => function ($builder) {
                    $builder->select(['member_id', 'is_agent', 'status', 'wechat', 'deleted_at', 'inviter']);
                }, 'orders'         => function ($order) {
                    $order->select(['id', 'uid', 'price', 'status'])->where('status', 3);
                }, 'memberChildren' => function ($member) {
                    $member->select(['id', 'child_id', 'level', 'member_id'])->where('level', 1)
                        ->with(['orders' => function ($order) {
                            $order->select(['id', 'uid', 'price', 'status'])->where('status', 3);
                        }]);
                }])
                ->orderBy('uid', 'desc')
                ->paginate($pageSize)
                ->toArray();
        }

        foreach ($teamMembers['data'] as &$v) {
            $v['team_order_money'] = round(collect($v['member_children'])->sum(function ($member_children) {
                return collect($member_children['orders'])->sum('price');
            }), 2);
            $v['team_total'] = collect($v['member_children'])->count();

            //会员自己
            $v['child_order_money'] = round(collect($v['orders'])->sum('price'), 2);
            $v['child_order_total'] = collect($v['orders'])->count();

            $v['avatar'] = $v['avatar_image'];
            unset($v['avatar_image']);
            $v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);

            $v['id'] = $v['uid'];
            unset($v['uid']);
            $v['wechat'] = $v['yz_member']['wechat'] ?: 0;
            $v['mobile'] = $v['mobile'] ?: 0;
            $v['realname'] = $v['realname'] ?: 0;
            $v['inviter'] = empty($v['yz_member']['inviter']) ? 1 : 0;

            if (!is_null($v['yz_member'])) {
                if (1 == $v['yz_member']['is_agent'] && 2 == $v['yz_member']['status']) {
                    $v['is_agent'] = 1;
                }
            }
            unset($v['yz_member']);
            unset($v['member_children']);
            unset($v['orders']);

        }
        $data = $teamMembers;

        return show_json(1, $data);
    }

    /**
     * 我推荐的人 v2 基本信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyAgent_v2()
    {
        set_time_limit(0);
        $total = 0;
        $relation_base = \Setting::get('relation_base');

        //如果没有设置则显示
        if (empty($relation_base)) {
            $relation_base['relation_level'][0] = true;
            $relation_base['relation_level'][1] = true;
        }

        for ($i=1; $i<=2; $i++) {
            $agent_count = MemberChildren::where('member_id', $this->member_id)
                ->where('level', $i)
                ->count();

            $total += $agent_count;
            $data['level'.$i] = [
                'level'   => $relation_base['relation_level']['name'.$i] ?: $i.'级',
                'total'   => $agent_count,
                'is_show' => $relation_base['relation_level'][$i-1] ? true : false,
                'level_p' => 2,
            ];
        }

        $data['total'] = $total;

        return show_json(1, $data);

    }

    /**
     * 我的推荐人v2
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyReferral_v2()
    {
        ini_set('memory_limit', -1);

        $member_id = \YunShop::app()->getMemberId();
        $member_info = $this->mc_member->toArray();
        $unicid = \YunShop::app()->uniacid;
        $set = \Setting::get('shop.member');
        $member_set = \Setting::get('relation_base');

        if (isset($set) && $set['headimg']) {
            $avatar = replace_yunshop(yz_tomedia($set['headimg']));
        } else {
            $avatar = Url::shopUrl('static/images/photo-mr.jpg');
        }
        //IOS时，把微信头像url改为https前缀
        $avatar = ImageHelper::iosWechatAvatar(yz_tomedia($avatar));;

        $member_info['avatar'] = yz_tomedia($member_info['avatar']);;
        //查询上级
        $referrer_info = MemberModel::getMyReferrerInfo($member_info['yz_member']['parent_id'])->first();

        if ($member_info['yz_member']['inviter'] == 1) {
            if (!empty($referrer_info)) {
                $info = $referrer_info->toArray();
                $data = [
                    'uid'             => $info['uid'],
                    'avatar'          => $info['avatar'],
                    'nickname'        => $info['nickname'],
                    'level'           => $info['yz_member']['level']['level_name'],
                    'is_show'         => $member_set['is_referrer'] ?: 0,
                    'referrer_phone'  => $info['mobile'],
                    'referrer_wechat' => $info['yz_member']['wechat'],
                ];
            } else {
                $data = [
                    'uid'      => '',
                    'avatar'   => $avatar,
                    'nickname' => '总店',
                    'level'    => '',
                    'is_show'  => $member_set['is_referrer'] ?: 0,
                ];
            }
        } else {
            $data = [
                'uid'      => '',
                'avatar'   => $avatar,
                'nickname' => '暂无',
                'level'    => '',
                'is_show'  => $member_set['is_referrer'] ?: 0,
            ];
        }

        //---------------------new-----------------------
        //团队1级会员 防止会员删除
        $data['child_total'] = DB::table('yz_member_children')
            ->join('yz_member', function ($join) {
                $join->on('yz_member.member_id', '=', 'yz_member_children.child_id')
                    ->whereNull('deleted_at');
            })
            ->where('yz_member_children.uniacid',$unicid)
            ->where('yz_member_children.member_id',$member_id)
            ->where('level',1)
            ->count();

        $level_childs = MemberChildren::where('member_id', $this->member_id)->where("level",1)->pluck("child_id");
        $team_order_money = 0 ;
        if (!empty($level_childs)) {

            if(app('plugins')->isEnabled('agent-list-set') && \Setting::get('plugin.agent-liset-set.pay_order')){
                $team_order_money = SetController::getChildOrderMoney($member_id, $unicid);  //统计已完成+已支付订单+自己的订单
            } else {
                $team_order_money = MemberChildren::select(['yz_member_children.child_id', 'yz_member_children.member_id'])
                    ->join('yz_order','yz_member_children.child_id','=','yz_order.uid')
                    ->where('yz_order.status',3)
                    ->where('yz_member_children.level',1)
                    ->where('yz_member_children.member_id',$member_id)
                    ->sum('yz_order.price');
            }

        }
        $data['child_order_money'] = round($team_order_money,2);

        //团队会员
        $data['team_total'] = DB::table('yz_member_children')
            ->join('yz_member', function ($join) {
                $join->on('yz_member.member_id', '=', 'yz_member_children.child_id')
                    ->whereNull('deleted_at');
            })
            ->where('yz_member_children.uniacid',$unicid)
            ->where('yz_member_children.member_id',$member_id)
            ->count();

        $childs = MemberChildren::where('member_id', $this->member_id)->pluck("child_id");
        $team_all = 0;
        if (!empty($childs)) {
            if(app('plugins')->isEnabled('agent-list-set') && \Setting::get('plugin.agent-liset-set.pay_order')){
                $team_all = SetController::getAllOrderMoney($member_id, $unicid);  //统计已完成+已支付订单+自己的订单
            } else {
                $team_all = MemberChildren::select(['yz_member_children.child_id', 'yz_member_children.member_id'])
                    ->join('yz_order','yz_member_children.child_id','=','yz_order.uid')
                    ->where('yz_order.status',3)
                    ->where('yz_member_children.member_id',$member_id)
                    ->sum('yz_order.price');
            }
        }
        $data['team_order_money'] = round($team_all,2);

        $team_goods_total = 0;
        if (!empty($childs)) {
            $team_goods_total = MemberChildren::select(['yz_member_children.child_id', 'yz_member_children.member_id'])
                ->join('yz_order','yz_member_children.child_id','=','yz_order.uid')
                ->where('yz_order.status','>=',1)
                ->where('yz_member_children.member_id',$member_id)
                ->sum('yz_order.goods_total');
        }

        $data['team_goods_total'] = intval($team_goods_total);
        //---------------------new-----------------------


        $data['self'] = $member_info;
        $data['is_recommend_wechat'] = $member_set['is_recommend_wechat'] ?: 0;
        $data['wechat'] = $member_set['relation_level']['wechat'] ?: 0;
        $data['phone'] = $member_set['relation_level']['phone'] ?: 0;
        $data['realname'] = $member_set['relation_level']['realname'] ?: 0;
        $data['name1'] = $member_set['relation_level']['name1'] ?: '';
        $data['name2'] = $member_set['relation_level']['name2'] ?: '';
        $data['name3'] = $member_set['relation_level']['name3'] ?: '';

        if (!empty($data)) {
            return show_json(1, $data);

        } else {
            return show_json(0, '会员不存在');
        }
    }

    /**
     * 会员推荐人上级
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getMyReferralParents()
    {
        if($this->yz_member['inviter'] == 1 && !empty(MemberShopInfo::getMemberShopInfo($this->yz_member['parent_id']))){
            $data = MemberParent::getAgentParentByMemberId($this->yz_member['parent_id']);
            return show_json(1, $data);
        }else{
            return show_json(1, ['is_show' => 0]); //没有推荐人上级
        }
    }

}