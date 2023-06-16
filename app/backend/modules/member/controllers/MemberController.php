<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/2
 * Time: 下午2:03
 */

namespace app\backend\modules\member\controllers;

use app\backend\modules\charts\modules\phone\models\PhoneAttribution;
use app\backend\modules\charts\modules\phone\services\PhoneAttributionService;
use app\backend\modules\member\models\McMappingFans;
use app\backend\modules\member\models\Member;
use app\backend\modules\member\models\MemberChildren;
use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\backend\modules\member\models\MemberParent;
use app\backend\modules\member\models\MemberRecord;
use app\backend\modules\member\models\MemberShopInfo;
use app\backend\modules\member\models\MemberUnique;
use app\backend\modules\member\services\HandleNickname;
use app\common\events\member\MergeMemberEvent;
use app\common\exceptions\ShopException;
use app\common\models\member\MemberMerge;
use app\common\models\Order;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\services\finance\PointService;
use app\common\services\member\MemberMergeService;
use app\common\services\Session;
use app\backend\modules\member\services\FansItemService;
use app\frontend\modules\member\services\MemberService;
use app\common\components\BaseController;
use app\common\events\member\MemberDelEvent;
use app\common\events\member\MemberLevelUpgradeEvent;
use app\common\events\member\MemberRelationEvent;
use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\helpers\Cache;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\common\models\Member as Members;
use app\common\models\member\MemberChangeMobileLog;
use app\common\models\member\MemberMergeLog;
use app\common\models\member\ParentOfMember;
use app\common\models\MemberAlipay;
use app\common\models\MemberGroup as Member_Group;
use app\common\models\MemberMiniAppModel;
use app\common\models\MemberShopInfo as MemberShop_Info;
use app\common\models\MemberWechatModel;
use app\common\services\ExportService;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\SubMemberModel;
use app\frontend\modules\member\models\SubMemberModel as SubMember_Model;
use app\Jobs\ChangeMemberRelationJob;
use app\Jobs\ModifyRelationshipChainJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yunshop\Love\Common\Models\MemberLove;
use Yunshop\Love\Common\Services\LoveChangeService;
use Yunshop\MemberTags\Common\models\MemberTagsModel;
use Yunshop\TeamDividend\models\TeamDividendAgencyModel;
use Yunshop\TeamDividend\models\TeamDividendLevelModel;
use Illuminate\Support\Facades\Schema;
use app\common\events\member\MemberLevelDemotionEvent;


class MemberController extends BaseController
{
    private $pageSize = 20;
    protected $publicAction = ['addMember'];
    protected $ignoreAction = ['addMember'];
    private $exportRoute = "member.member.index";

    public function index()
    {
        $set = Setting::getByGroup('pay_password') ?: [];
        $member_tag = [];
        if (app('plugins')->isEnabled('member-tags')) {
            $member_tag = MemberTagsModel::uniacid()->select(['id','title','type'])->get();
        }
        $groups = MemberGroup::uniacid()->select(['id','group_name'])->get();
        $levels = MemberLevel::uniacid()->select(['id','level','level_name'])->get();
        return view('member.index', [
            'is_verify' => !empty($set['withdraw_verify']['is_member_export_verify'])?true:false,
            'expire_time' => Session::get('withdraw_verify')?:null,
            'verify_phone' => $set['withdraw_verify']['phone']?:"",
            'verify_expire' => $set['withdraw_verify']['verify_expire']?intval($set['withdraw_verify']['verify_expire']):10,
            'member_tag' => $member_tag,
            'groups' => $groups,
            'levels' => $levels,
        ])->render();
    }

    public function show()
    {
        $parames = \YunShop::request();
        $list = Member::searchNewMembers($parames);
        if ($parames['search']['first_count'] ||
            $parames['search']['second_count'] ||
            $parames['search']['third_count'] ||
            $parames['search']['team_count']
        ) {
            //------------------new-------------------
            $result_ids = [];
            if ($parames['search']['first_count']) {
                $result_ids = $this->getChildCount($parames['search']['first_count'], 1);
            }
            if ($parames['search']['second_count']) {
                $second_ids = $this->getChildCount($parames['search']['second_count'], 2);
                $result_ids = empty($result_ids) ? $second_ids : array_intersect($result_ids, $second_ids);
                unset($second_ids);
            }
            if ($parames['search']['third_count']) {
                $third_ids = $this->getChildCount($parames['search']['third_count'], 3);
                $result_ids = empty($result_ids) ? $third_ids : array_intersect($result_ids, $third_ids);
                unset($third_ids);
            }
            if ($parames['search']['team_count']) {
                $team_ids = $this->getChildCount($parames['search']['team_count']);
                $result_ids = empty($result_ids) ? $team_ids : array_intersect($result_ids, $team_ids);
                unset($team_ids);
            }
            $list = $list->whereIn('uid', $result_ids);
        }
        $list = $list->orderBy('uid', 'desc')
            ->paginate($this->pageSize)
            ->toArray();
        foreach ($list['data'] as $key => $item) {
            $list['data'][$key]['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
        }
        $is_customers = 0;
        if (app('plugins')->isEnabled('wechat-customers')) {
            $is_customers = 1;
        }
        $is_member_tags = 0;
        if (app('plugins')->isEnabled('member-tags')) {
            $res = array_pluck(Setting::getAllByGroup('member-tags')->toArray(), 'value', 'key');
            if ($res['is_open'] == 1) $is_member_tags = 1;
        }
        $list = (new FansItemService())->setFansItem($list);
        $level = MemberLevel::uniacid()->select('id', 'level', 'level_name')->get()->toArray();
        $member_group = MemberGroup::uniacid()->select('id', 'group_name')->get()->toArray();
        $shop_set = \Setting::get('shop.member');
        $level[] = [
            'id' => 0,
            'level' => 0,
            'level_name' => $shop_set['level_name'] ?: '普通会员',
        ];
        $member_group[] = [
            'id' => 0,
            'group_name' => '无分组'
        ];
        $datas = [
            'list' => $list,
            'rest' => [
                'default_level_name' => $shop_set['level_name'] ?: '普通会员',
                'total' => $list['current_page'] <= $list['last_page'] ? $list['total'] : 0,
                'opencommission' => 1,
                'is_customers' => $is_customers,
                'is_member_tags' => $is_member_tags,
            ],
            'level' => $level,
            'member_group' => $member_group
        ];
        return $this->successJson('ok', $datas);
    }

    /**
     * 获取$level级下线人数超过$countNum的会员id
     * @param int $countNum
     * @param string $level
     * @return array
     */
    public function getChildCount($countNum = 0, $level = '')
    {
        $model = MemberChildren::uniacid()->select(DB::raw('member_id,COUNT(*) as count_num'));
        if (!empty($level)) {
            $model->where('level', $level);
        }
        $ids = $model->groupBy('member_id')->having('count_num', '>=', $countNum)
            ->get()->toArray();
        return array_column($ids, 'member_id');
    }

    //会员下线导出
    public function doExport()
    {
        if (!$this->checkVerify()) {
            return $this->message('二次校验失败','','error');
        }
        $export_page = request()->export_page ? request()->export_page : 1;
        //清除之前没有导出的文件
        if ($export_page == 1) {

            $fileNameArr = file_tree(storage_path('exports'));
            foreach ($fileNameArr as $val) {
                if (file_exists(storage_path('exports/' . basename($val)))) {
                    unlink(storage_path('exports/') . basename($val)); // 路径+文件名称
                }
            }
        }
        $request = \YunShop::request();
        $member_info = Member::getUserInfos($request->id)->first();
        if (empty($member_info)) {
            return $this->message('会员不存在', '', 'error');
        }
        $list = Member::getAgentInfoByMemberId($request);
        if ($list->get()->isEmpty()) {
            return $this->message('订单导出数据为空', '', 'error');
        }
        $export_model = new ExportService($list, $export_page);
        if (!$export_model->builder_model->isEmpty()) {
            $file_name = date('Ymdhis', time()) . '订单导出';//返现记录导出
            $export_data[0] = ['会员ID', '推荐人', '粉丝', '姓名', '手机号码', '状态', '下线状态', '注册时间', '关注'];
            $status_name = [
                0 => "未审核",
                1 => "审核中",
                2 => "已审核"
            ];
            $inviter_name = [
                0 => "暂定下线",
                1 => "锁定关系下线"
            ];
            foreach ($export_model->builder_model->toArray() as $key => $value) {
                if (empty($value['has_one_fans']['followed'])) {
                    if (empty($value['has_one_fans']['uid'])) {
                        $follow = "未关注";
                    } else {
                        $follow = "取消关注";
                    }
                } else {
                    $follow = "已关注";
                }
                $export_data[$key + 1] = [
                    $value['uid'],
                    $value['yz_member']['agent']['nickname'],
                    $value['nickname'],
                    $value['realname'],
                    $value['mobile'],
                    $value['yz_member']['is_agent'] == 0 ? "-" : $status_name[$value["yz_member"]['status']],
                    $inviter_name[$value['yz_member']['inviter']],
                    date("Y-m-d H:i", $value['createtime']),
                    $follow
                ];
            }
            $export_model->export($file_name, $export_data, 'member.member.agent-old');
        }

    }

    /**
     * 加载模板 -- 添加会员
     * @return string
     * @throws \Throwable
     */
    public function addMember()
    {
        return view('member.add-member', [])->render();
    }

    public function addMemberData()
    {
        $item = request()->item;
        $mobile = \YunShop::request()['mobile'];
        $password = \YunShop::request()['password'];
        $uniacid = \YunShop::app()->uniacid;
        $member = Setting::get('shop.member');
        //获取图片
        $member_set = \Setting::get('shop.member');
        if (isset($member_set) && $member_set['headimg']) {
            $avatar = yz_tomedia($member_set['headimg']);
        } else {
            $avatar = Url::shopUrl('static/images/photo-mr.jpg');
        }
        if ((\Request::getMethod() == 'POST') && ($item == 1)) {
            //判断是否已注册
            $member_info = MemberModel::getId($uniacid, $mobile);
            if (!empty($member_info)) {
                throw new AppException('该手机号已被注册');
            }
            //添加mc_members表
            $default_groupid = Member_Group::getDefaultGroupId($uniacid)->first();
            $data = array(
                'uniacid' => $uniacid,
                'mobile' => $mobile,
                'groupid' => $default_groupid->id ? $default_groupid->id : 0,
                'createtime' => time(),
                'nickname' => $mobile,
                'avatar' => $avatar,
                'gender' => 0,
                'residecity' => '',
            );
            //随机数
            $data['salt'] = Str::random(8);
            //加密
            $data['password'] = md5($password . $data['salt']);
            $memberModel = MemberModel::create($data);
            $member_id = $memberModel->uid;
            //手机归属地查询插入
            $phoneData = file_get_contents((new PhoneAttributionService())->getPhoneApi($mobile));
            $phoneArray = json_decode($phoneData);
            $phone['uid'] = $member_id;
            $phone['uniacid'] = $uniacid;
            $phone['province'] = $phoneArray->data->province;
            $phone['city'] = $phoneArray->data->city;
            $phone['sp'] = $phoneArray->data->sp;
            $phoneModel = new PhoneAttribution();
            $phoneModel->updateOrCreate(['uid' => $member_id], $phone);
            //默认分组表
            //添加yz_member表
            $default_sub_group_id = Member_Group::getDefaultGroupId()->first();
            if (!empty($default_sub_group_id)) {
                $default_subgroup_id = $default_sub_group_id->id;
            } else {
                $default_subgroup_id = 0;
            }
            $sub_data = array(
                'member_id' => $member_id,
                'uniacid' => $uniacid,
                'group_id' => $default_subgroup_id,
                'level_id' => 0,
                'invite_code' => \app\frontend\modules\member\models\MemberModel::generateInviteCode(),
                'system_type' => 'pc',
                'parent_id' => 0,
                'inviter' => 1,
            );
            //添加用户子表
            SubMember_Model::insertData($sub_data);
            $record_data = [
                'uid' => $member_id,
                'parent_id' => 0,
                'after_parent_id' => 0,
                'status' => 1,
                'uniacid' => \YunShop::app()->uniacid
            ];
            //记录
            MemberRecord::create($record_data);
            $password = $data['password'];
            $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();
            $yz_member = MemberShop_Info::getMemberShopInfo($member_id)->toArray();
            $data = MemberModel::userData($member_info, $yz_member);
            return $this->successJson('添加用户成功', ['data' => true]);
        }
        if (empty($member['headimg'])) {
            $val = static_url('resource/images/nopic.jpg');
            $headimg = yz_tomedia($val);
        } else {
            $headimg = yz_tomedia($member['headimg']);
        }
        return $this->successJson('ok', ['img' => $headimg]);
    }

    public function import()
    {
        return view('member.import')->render();
    }

    public function memberExcelDemo()
    {
        $exportData['0'] = ['手机号', '密码'];
        $file_name = '会员批量导入模板';
        app('excel')->store(new \app\exports\FromArray($exportData), $file_name . '.xlsx', 'export');
        app('excel')->download(new \app\exports\FromArray($exportData), $file_name . '.xlsx')->send();
    }

    public function memberExcel()
    {
        $data = request()->input();
        $uniacid = \YunShop::app()->uniacid;
        //excel 本身就重复的值
        if (!$data['data']['0']['手机号']) {
            $this->errorJson('第一项开头必须为手机号');
        }
        if (!$data['data']['0']['密码']) {
            $this->errorJson('第二项开头必须为密码');
        }
        $data = array_column($data['data'], null, '手机号');
        foreach ($data['data'] as $key => $value) {
            if (!preg_match("/^1[3456789]{1}\d{9}$/", $value['手机号'])) {
                unset($data[$key]);
            }
        }
        $phone = array_keys($data);
        $phones = MemberModel::uniacid()->select('mobile')->whereIn('mobile', $phone)->pluck('mobile');
        if (!empty($phones)) {
            // 存在重复值 取交集
            $repeatPhone = array_intersect($phone, $phones->toArray());
            //删除重复值
            foreach ($repeatPhone as $value) {
                if (isset($data[$value])) {
                    unset($data[$value]);
                }
            }
            if (empty($data)) {
                return $this->errorJson('表格所有信息已存在，请勿重复导入!');
            }
        }
        $defaultGroupId = Member_Group::getDefaultGroupId()->first();
        $defaultSubGroupId = $defaultGroupId->id ?: 0;

        //整理数据入库
        $i = 0;
        $array = array();
        //获取图片
        $memberSet = \Setting::get('shop.member');
        \Log::info('member_set', $memberSet);
        if (isset($memberSet) && $memberSet['headimg']) {
            $avatar = yz_tomedia($memberSet['headimg']);
        } else {
            $avatar = Url::shopUrl('static/images/photo-mr.jpg');
        }
        foreach ($data as $v) {
            $salt = Str::random(8);
            $array[$i] = [
                'uniacid' => $uniacid,
                'mobile' => $v['手机号'],
                'groupid' => $defaultSubGroupId,
                'createtime' => $_SERVER['REQUEST_TIME'],
                'nickname' => $v['手机号'],
                'avatar' => $avatar,
                'gender' => 0,
                'residecity' => '',
                'salt' => $salt,
                'password' => md5($v['密码'] . $salt),
            ];
            $i++;
        }
        if (!MemberModel::insert($array)) {
            return $this->errorJson('批量添加失败');
        }
        //todo 批量插入同时无法返回主键ID故查询一次
        $idArray = MemberModel::uniacid()->select('uid', 'mobile')->whereIn('mobile', array_keys($data))->get();

        $subData = [];
        $change_log = [];
        foreach ($idArray as $key => $value) {
            $subData[$key] = array(
                'member_id' => $value->uid,
                'uniacid' => $uniacid,
                'group_id' => $defaultSubGroupId,
                'level_id' => 0,
                'parent_id' => 0,
                'inviter' => 1,
                'invite_code' => \app\frontend\modules\member\models\MemberModel::generateInviteCode(),
                'created_at' => $_SERVER['REQUEST_TIME'],
                'updated_at' => $_SERVER['REQUEST_TIME']
            );
            $change_log[$key] = [
                'uid' => $value->uid,
                'parent_id' => 0,
                'after_parent_id' => 0,
                'status' => 1,
                'uniacid' => \YunShop::app()->uniacid,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }
        if (SubMember_Model::insert($subData) && MemberRecord::insert($change_log)) {
            return $this->successJson('导入成功', ['data' => true]);
        } else {
            return $this->errorJson('导入失败');
        }
    }


    private function getResultIds(array $result_ids, $member_id, $compare, $compared, $is_added)
    {
        if ($compare < $compared) {
            ($is_added && !in_array($member_id, $result_ids)) && $result_ids[] = $member_id;
        } else {
            $key = array_search($member_id, $result_ids);
            $key !== false && array_splice($result_ids, $key, 1);
        }
        return $result_ids;
    }

    private function getMembersLower($memberId, $level = '')
    {
        $array = $level ? [$memberId, $level] : [$memberId];
        $condition = $level ? ' = ?' : '';
        return MemberShopInfo::select('member_id')->whereRaw('FIND_IN_SET(?,relation)' . $condition, $array)->count();
    }

    private function getMemberTeam($memberId)
    {
        $first = MemberShopInfo::select('member_id', 'parent_id')->where('parent_id', $memberId)->get();

        $result_ids = [];
        if ($first) {
            foreach ($first as $key => $member) {
                $result_ids[] = $member->member_id;
                $second = MemberShopInfo::select('member_id', 'parent_id')->where('parent_id', $member->member_id)->get();
                if ($second) {
                    $ids = $this->getMemberTeamRecursion($second);
                    $result_ids = array_merge($result_ids, $ids);
                }
            }
        }

        return count($result_ids);
    }

    private function getMemberTeamRecursion($memberIds)
    {
        $result_ids = [];
        foreach ($memberIds as $key => $member) {
            $result_ids[] = $member->member_id;
            $first = MemberShopInfo::select('member_id', 'parent_id')->where('parent_id', $member->member_id)->get();
            if ($first) {
                $ids = $this->getMemberTeamRecursion($first);
                $result_ids = array_merge($result_ids, $ids);
            }
        }

        return $result_ids;
    }


    /**
     * 详情
     *
     */
    public function detail()
    {
        $exists_groupId = false;
        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;
        $type = request()->type ? 1 : 0;    //用来判断返回模板还是json数据
        if ($uid == 0 || !is_int($uid)) {
            $this->message('参数错误', '', 'error');
            exit;
        }
        $member = Member::getMemberDetailBlackById($uid);

        if (!$member) {
            $this->message('该会员已被删除或者已注销', '', 'error');
            exit;
        }

        if (!$member->avatar) {
            if (Setting::get('shop.member')['headimg_url']) {
                $member->avatar = yz_tomedia(Setting::get('shop.member')['headimg_url']);
            } else {
                $member->avatar = yz_tomedia(Setting::get('shop.shop')['logo']);
            }
        }
        if (!$member->nickname && $member->mobile) {
            $member->nickname = substr($member->mobile, 0, 2) . '*******' . substr($member->mobile, -2);
        }
        $groups = MemberGroup::getMemberGroupList();
        $levels = MemberLevel::uniacid()->select('id', 'level', 'level_name')->get();
        $myform = [];
        if (!empty($member)) {
            $member = $member->toArray();
            if ($member['yz_member']['is_agent'] == 1 && $member['yz_member']['status'] == 2) {
                $member['agent'] = 1;
            } else {
                $member['agent'] = 0;
            }
            $member['createtime'] = date('Y-m-d H:i:s', $member['createtime']);
            $myform = json_decode($member['yz_member']['member_form'], true);
        }
        $set = \Setting::get('shop.member');
        if (empty($set['level_name'])) {
            $set['level_name'] = '普通会员';
        }
        if ($member['yz_member']['parent_id'] == 0) {
            $parent_name = '总店';
        } else {
            $parent = Member::getMemberById($member['yz_member']['parent_id']);
            $parent_name = $parent->nickname;
        }
        if (!$member['yz_member']['validity']) {
            $member['yz_member']['validity'] = 0;
        }
        foreach($groups as $rows) {
            if ($rows['id']== $member['yz_member']['group_id']) {
                $exists_groupId = true;
            }
        }
        if (!$exists_groupId) {
            $member['yz_member']['group_id'] = 0;
        }
        if ($type) {
            return $this->successJson('ok', [
                'member' => $member,
                'levels' => $levels,
                'groups' => $groups,
                'set' => $set,
                'myform' => $myform,
                'parent_name' => $parent_name
            ]);
        }
        return view('member.detail1', [
            'member' => $member,
            'levels' => $levels,
            'groups' => $groups,
            'set' => $set,
            'myform' => $myform,
            'parent_name' => $parent_name
        ])->render();
    }


    /**
     * 更新
     *
     */
    public function update()
    {
        $uid = request()->id;
        if ($uid == 0 || !is_numeric($uid)) {
            return $this->errorJson('参数错误');
        }

        $shopInfoModel = MemberShopInfo::getMemberShopInfo($uid) ?: new MemberShopInfo();

        $parame = request()->member;

        $invite_code = $parame['invite_code'];
        if ($invite_code == '') {
            $invite_code = '';
        } else {
            if (strlen($invite_code) != 8) {
                return $this->errorJson('会员邀请码8个字符');
            }
            /*  if(preg_match("/^[a-zA-Z\s]+$/",$invite_code) || preg_match("/^[0-9\s]+$/",$invite_code)){
                  return $this->message('会员邀请码必须大写字母拼加数字组成8个字符', '', 'error');
              }*/
            if (preg_match("/^[0-9a-zA-Z\s]+$/", $invite_code)) {
                $invite_code = strtoupper($invite_code);
            }
            $invite = MemberShopInfo::select('invite_code')
                ->where('invite_code', $invite_code)
                ->where('member_id', '!=', $uid)
                ->count();
            if ($invite) {
                return $this->errorJson('会员邀请码已存在或参数错误');
            }
        }


        $member_model = Member::find($uid);
        $member_model->widgets = $parame['widgets'];

        $member_model->realname = $parame['realname'];

        $member_model->nickname = $parame['nickname'];
        $member_model->avatar = $parame['avatar'];

        $member_model->save();
//        $mc = [
//            'realname' => $parame['realname']
//        ];
//        Member::updateMemberInfoById($mc, $uid);


        $yz = [
            'member_id' => $uid,
            'wechat' => $parame['wechat'],
            'uniacid' => \YunShop::app()->uniacid,
            'level_id' => $parame['level_id'] ?: 0,
            'group_id' => $parame['group_id'],
            'alipayname' => $parame['alipayname'],
            'alipay' => $parame['alipay'],
            'is_black' => $parame['is_black'],
            'content' => $parame['content'],
            'custom_value' => $parame['custom_value'],
            'validity' => $parame['validity'] ? $parame['validity'] : 0,
            'invite_code' => $invite_code,
        ];

        if ($parame['agent']) {
            $yz['is_agent'] = 1;
            $yz['status'] = 2;
            $yz['agent_time'] = !empty($shopInfoModel->agent_time) ? $shopInfoModel->agent_time : time();
            if ($shopInfoModel->inviter == 0) {
                $shopInfoModel->inviter = 1;
                $shopInfoModel->parent_id = 0;
            }
        } else {
            $yz['is_agent'] = 0;
            $yz['status'] = 0;
            $yz['agent_time'] = 0;
        }

        //判断会员等级是否升级
        $is_upgrade = false;

        $new_level = MemberLevel::find($yz['level_id'])->level;
        if ($shopInfoModel->level_id != $yz['level_id'] && $new_level > $shopInfoModel->level->level) {
            $is_upgrade = true;
        }
        $data['customDatas'] = request()->myform;
        //自定义表单
        $member_form = (new MemberService())->updateMemberForm($data);

        if (!empty($member_form)) {
            $yz['member_form'] = json_encode($member_form);
        }

        $shopInfoModel->fill($yz);
        $validator = $shopInfoModel->validator();

        // 如果会员期限延长, 那么就清理降级时间
        if ($shopInfoModel->validity > 0) {
            $shopInfoModel->downgrade_at = 0;
        }

        if ($validator->fails()) {
            $this->error($validator->messages());
        } else {
            if ($shopInfoModel->save()) {
                if ($is_upgrade) {
                    //会员等级升级触发事件
                    event(new MemberLevelUpgradeEvent($shopInfoModel, true));
                } else {
                    //会员等级降级
                    event(new MemberLevelDemotionEvent($shopInfoModel));
                }

                if ($parame['agent']) {
                    $member = Member::getMemberByUid($uid)->with('hasOneFans')->first();
                    event(new MemberRelationEvent($member));
                }
                return $this->successJson("用户资料更新成功");
            }
        }
        return $this->errorJson("用户资料更新失败");
    }

    /**
     * 删除
     *
     */
    public function delete()
    {
        $del = false;

        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;

        if ($uid == 0 || !is_int($uid)) {
            return $this->message('参数错误', '', 'error');
        }

        $member = Member::getMemberBaseInfoById($uid);

        if (empty($member)) {
            return $this->message('用户不存在', '', 'error');
        }


        $del = DB::transaction(function () use ($uid, $member) {
            //商城会员表
            //MemberShopInfo::deleteMemberInfoById($uid);

            //unionid关联表
            if (isset($member->hasOneFans->unionid) && !empty($member->hasOneFans->unionid)) {
                $uniqueModel = MemberUnique::getMemberInfoById($member->hasOneFans->unionid)->first();

                if (!is_null($uniqueModel)) {
                    if ($uniqueModel->member_id != $uid) {
                        //删除会员
                        Member::UpdateDeleteMemberInfoById($uniqueModel->member_id);
                        //小程序会员表
                        MemberMiniAppModel::deleteMemberInfoById($uniqueModel->member_id);
                        //app会员表
                        MemberWechatModel::deleteMemberInfoById($uniqueModel->member_id);

                        //删除微擎mc_mapping_fans 表数据
                        McMappingFans::deleteMemberInfoById($uniqueModel->member_id);

                        //清空 yz_member 关联
                        MemberShopInfo::deleteMemberInfoOpenid($uniqueModel->member_id);
                        //Member::deleteMemberInfoById($uniqueModel->member_id);
                    }
                }
            }

            MemberUnique::deleteMemberInfoById($uid);

            if (app('plugins')->isEnabled('alipay-onekey-login')) {
                //删除支付宝会员表
                MemberAlipay::deleteMemberInfoById($uid);
            }

            //小程序会员表
            MemberMiniAppModel::deleteMemberInfoById($uid);

            //app会员表
            MemberWechatModel::deleteMemberInfoById($uid);

            //删除微擎mc_mapping_fans 表数据
            McMappingFans::deleteMemberInfoById($uid);

            //清空 yz_member 关联
            MemberShopInfo::deleteMemberInfoOpenid($uid);

            //删除会员
            Member::UpdateDeleteMemberInfoById($uid);

            event(new MemberDelEvent($uid));

            return true;
        });

        if ($del) {
            return $this->message('用户删除成功', yzWebUrl('member.member.index'));
        }

        return $this->message('用户删除失败', yzWebUrl('member.member.index'), 'error');
    }

    /**
     * 设置黑名单
     *
     */
    public function black()
    {
        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;

        if ($uid == 0 || !is_int($uid)) {
            $this->message('参数错误', '', 'error');
            exit;
        }

        $data = array(
            'is_black' => \YunShop::request()->black
        );

        if (MemberShopInfo::setMemberBlack($uid, $data)) {
            (new \app\common\services\operation\MemberBankCardLog(['uid' => $uid, 'is_black' => \YunShop::request()->black], 'special'));
            return $this->message('黑名单设置成功', yzWebUrl('member.member.index'));
        } else {
            return $this->message('黑名单设置失败', yzWebUrl('member.member.index'), 'error');
        }
    }

    /**
     * 获取搜索会员
     * @return html
     */
    public function getSearchMember()
    {

        $keyword = \YunShop::request()->keyword;
        $member = Member::getMemberByName($keyword);
        $member = set_medias($member, array('avatar', 'share_icon'));
        return view('member.query', [
            'members' => $member->toArray(),
        ])->render();
    }

    /**
     * 获取搜索会员(json)
     * @return html
     */
    public function getSearchMemberJson()
    {
        $keyword = request()->keyword;
        $member = Member::getMemberJsonByName($keyword);
        $member = set_medias($member, array('avatar', 'share_icon'));
        return $this->successJson('请求接口成功', [
            'members' => $member->toArray(),
        ]);
    }


    /**
     * 获取搜索会员(json)
     * @return html
     */
    public function searchMemberList()
    {
        $keyword = request()->keyword;
        $member = Member::uniacid()->searchLike($keyword)
            ->select('uid', 'nickname', 'avatar', 'mobile')
            ->whereHas('yzMember', function ($query) {
                $query->whereNull('deleted_at');
            })->paginate(10000);
        $member = set_medias($member, array('avatar', 'share_icon'));
        return $this->successJson('请求接口成功', $member->toArray());
    }

    /**
     * 加载模板 -- 直推客户
     * @return string
     * @throws \Throwable
     */
    public function agentOld()
    {
        $set = Setting::getByGroup('pay_password') ?: [];
        return view('member.agent-old', [
            'is_verify' => !empty($set['withdraw_verify']['is_member_export_verify'])?true:false,
            'expire_time' => Session::get('withdraw_verify')?:null,
            'verify_phone' => $set['withdraw_verify']['phone']?:"",
            'verify_expire' => $set['withdraw_verify']['verify_expire']?intval($set['withdraw_verify']['verify_expire']):10
        ])->render();
    }

    /**
     * 推广下线
     *
     * @return mixed
     */
    public function agentOldShow()
    {
        $request = \YunShop::request();

        $member_info = Member::memberCustomer($request->id)->first();

        if (empty($member_info)) {
            return $this->message('会员不存在', '', 'error');
        }

        $list = Member::getAgentInfoByMemberId($request)
            ->paginate($this->pageSize)
            ->toArray();

        return $this->successJson('ok', [
            'member' => $member_info,
            'list' => (new FansItemService())->setFansItem($list),
            'total' => $list['total'],
        ]);
    }

    /**
     * 加载模板 -- 推广下线
     * @return string
     * @throws \Throwable
     */
    public function agent()
    {
        $set = Setting::getByGroup('pay_password') ?: [];
        return view('member.agent', [
            'is_verify' => !empty($set['withdraw_verify']['is_member_export_verify'])?true:false,
            'expire_time' => Session::get('withdraw_verify')?:null,
            'verify_phone' => $set['withdraw_verify']['phone']?:"",
            'verify_expire' => $set['withdraw_verify']['verify_expire']?intval($set['withdraw_verify']['verify_expire']):10
        ])->render();
    }

    public function getLevels()
    {
        $levels = MemberParent::selectRaw('count(member_id) as total, level, max(parent_id) as parent_id')
            ->where('parent_id', \YunShop::request()->id)
            ->groupBy('level')
            ->get();
        return $this->successJson('ok', $levels);
    }
    /**
     * 推广下线
     *
     * @return mixed
     */
    public function agentShow()
    {
        $request = \YunShop::request();

        $member_info = Member::memberCustomer($request->id)->first();

        if (empty($member_info)) {
            return $this->error('会员不存在');
        }

        $list = MemberParent::children($request)
            ->orderBy('yz_member_parent.level', 'asc')
            ->orderBy('yz_member_parent.id', 'asc')
            ->paginate($this->pageSize)
            ->toArray();

        if (app('plugins')->isEnabled('team-dividend')) {
            $team_dividend_levels = TeamDividendLevelModel::uniacid()->select('level_name', 'id')->orderBy('level_weight', 'ASC')->get()->toArray();

            $level_count = MemberParent::children($request, 0)->join('yz_team_dividend_agency', function ($join) {
                $join->on('yz_team_dividend_agency.uid', '=', 'yz_member_parent.member_id')->whereNull('yz_team_dividend_agency.deleted_at');
            })->addSelect(DB::raw('COUNT(*) as level_people_count'), 'yz_team_dividend_agency.level')->groupBy('yz_team_dividend_agency.level')->get()->toArray();
            $level_count = array_column($level_count, 'level_people_count', 'level');
            foreach ($team_dividend_levels as &$team_dividend_level) {
                $team_dividend_level['count'] = $level_count[$team_dividend_level['id']] ?: 0;
            }

            unset($team_dividend_level);
        }else{
            $team_dividend_levels = [];
        }

        return $this->successJson('ok', [
            'member' => $member_info,
            'list' => (new FansItemService())->setAgentFansItem($list,array_column($team_dividend_levels,null,'id')),
            'total' => $list['total'],
            'request' => $request,
            'team_dividend_open' => app('plugins')->isEnabled('team-dividend'),
            'sum_amount'=>MemberParent::children($request,0)->join('yz_order',function ($join)use($request){
               $join->on('yz_order.uid','=','yz_member_parent.member_id')->where('yz_order.status',3);
                $time_search = in_array($request->time_search, ['create_time','finish_time']) ? $request->time_search : '';
                if ($time_search && $request->start_time && $request->end_time){
                    $join->whereBetween('yz_order.'.$time_search,[$request->start_time,$request->end_time]);
                }
            })->sum('yz_order.price'),
            'team_dividend_levels'=>$team_dividend_levels,
        ]);
    }

    public function agentExport()
    {
        ini_set('memory_limit','-1');
        if (!$this->checkVerify()) {
            return $this->message('二次校验失败','','error');
        }
        $request = request();
        $file_name = date('Ymdhis', time()) . '会员下级导出';
        $export_data[0] = ['ID', '昵称', '真实姓名', '电话', '会员注册时间', '业绩', '粉丝数', '推荐人', '推荐人ID'];

        if (app('plugins')->isEnabled('team-dividend')) {
            $export_data[0][]='经销商等级';
            $team_dividend_levels = TeamDividendLevelModel::uniacid()->select('level_name', 'id')->orderBy('level_weight', 'ASC')->get()->toArray();
            foreach ($team_dividend_levels as $v) {
                $export_data[0][] = '等级(' . $v['level_name'] . ')人数';
            }
            $agent_list = TeamDividendAgencyModel::uniacid()
                ->whereHas('hasOneMemberParent', function ($query) use ($request) {
                    $query->where('parent_id', $request->id);
                })->select('uid', 'level')->get();
        }else{
            $team_dividend_levels = [];
            $agent_list = null;
        }

        $list = MemberParent::children($request)
            ->orderBy('yz_member_parent.level', 'asc')
            ->orderBy('yz_member_parent.id', 'asc')
            ->get();

        $list->each(function ($v) use (&$team_dividend_levels, &$agent_list, &$export_data) {
            $child_ids = MemberChildren::uniacid()->where('yz_member_children.member_id', $v->member_id)
                ->join('yz_member', function ($join) {
                    $join->on('yz_member_children.child_id', '=', 'yz_member.member_id')->whereNull('deleted_at');
                })->pluck('child_id')->toArray();
            $team_dividend_level_data = [];
            if ($team_dividend_levels){
                //获取当前用户经销商等级
                $levelColumn='';
                if ($currentLevel=$agent_list->where('uid',$v->member_id)->first()) {
                    $index=array_search($currentLevel->level,array_column($team_dividend_levels,'id'));
                    if($index!==false){
                        $levelColumn=$team_dividend_levels[$index]['level_name'];
                    }
                }
                $team_dividend_level_data=array_merge($team_dividend_level_data,[$levelColumn]);
                $this_agent_list = $agent_list->whereIn('uid',$child_ids);
                foreach ($team_dividend_levels as $vv){
                    $team_dividend_level_data=array_merge($team_dividend_level_data,[$this_agent_list->where('level',$vv['id'])->count() ? : '0']);
                }
            }
            $export_data[] = array_merge([
                $v->member_id,
                $this->changeSpecialSymbols($v->hasOneChildMember->nickname ?: ''),
                $v->hasOneChildMember->realname ?: '',
                $v->hasOneChildMember->mobile ?: '',
                $v->hasOneChildMember->createtime ? date('Y-m-d H:i:s', $v->hasOneChildMember->createtime) : '',
                $v->order_price_sum ? : '0',
                count($child_ids) ?: '0',
                $this->changeSpecialSymbols($v->hasOneChildYzMember->hasOneParent->nickname ?: ''),
                $v->hasOneChildYzMember->hasOneParent->uid ?: '',
            ],$team_dividend_level_data);
        });


//        $member_id = request()->id;
//        $aid = request()->aid;
//        $keyword = request()->keyword;
//        $followed = request()->followed;
//        $isblack = request()->isblack;
//        $level = request()->level;
//        $export_page = request()->export_page ? request()->export_page : 1;
//        $child = MemberParent::select('yz_member_parent.*')
//            ->leftJoin('yz_member', 'yz_member_parent.member_id', '=', 'yz_member.member_id')
//            ->where('yz_member_parent.parent_id', $member_id);
//
//        if ($level) {
//            $child = $child->where('yz_member_parent.level', $level);
//        }
//
//        if (isset($isblack) && $isblack !== '') {
//            $child = $child->where('yz_member.is_black', intval($isblack));
//        }
//
//        if ($aid) {
//            $child = $child->where('yz_member_parent.member_id', intval($aid));
//        }
//        $child = $child->with('hasOneChildMember', 'hasOneChildFans');
//        $rows = 0;
//
//        foreach ($child->get() as $key => $item) {
//            $member = $item->hasOneChildMember;
//            $hasOneChildFans = $item->hasOneChildFans;
//
//            if (!$member) {
//                continue;
//            }
//
//            if ($followed == '1' && !$hasOneChildFans) {
//                continue;
//            }
//
//            if ($followed == '0' && $hasOneChildFans) {
//                continue;
//            }
//
//            if ($keyword && $keyword != $member->mobile && $keyword != $member->nickname && $keyword != $member->realname) {
//                continue;
//            }
//
//            $rows++;
//            $export_data[$rows] = [
//                $member->uid,
//                changeSpecialSymbols($member->nickname),
//                $member->realname,
//                $member->mobile,
//                date('Y-m-d H:i:s',$member->createtime),
//            ];
//        }
        // 此处参照商城订单管理的导出接口
        app('excel')->store(new \app\exports\FromArray($export_data), $file_name . '.xlsx', 'export');
        app('excel')->download(new \app\exports\FromArray($export_data), $file_name . '.xlsx')->send();
    }

    protected function changeSpecialSymbols($str)
    {
        $regex = "/\/|\～|\，|\。|\！|\？|\“|\”|\【|\】|\『|\』|\：|\；|\《|\》|\’|\‘|\ |\·|\~|\!|\@|\#|\\$|\%|\^|\&|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        return preg_replace($regex, '', $str);
    }

    /**
     * 导出验证
     * @return bool
     */
    private function checkVerify()
    {
        $set = Setting::getByGroup('pay_password')['withdraw_verify'] ?: [];
        if (empty($set) || empty($set['is_member_export_verify'])) {
            return true;
        }
        $verify = Session::get('withdraw_verify');  //没获取到
        if ($verify && $verify >= time()) {
            return true;
        }
        return false;
    }

    /**
     * 加载模板 -- 推广上线
     * @return string
     * @throws \Throwable
     */
    public function agentParent()
    {
        $set = Setting::getByGroup('pay_password') ?: [];
        return view('member.agent-parent', [
            'is_verify' => !empty($set['withdraw_verify']['is_member_export_verify'])?true:false,
            'expire_time' => Session::get('withdraw_verify')?:null,
            'verify_phone' => $set['withdraw_verify']['phone']?:"",
            'verify_expire' => $set['withdraw_verify']['verify_expire']?intval($set['withdraw_verify']['verify_expire']):10
        ])->render();
    }

    /**
     * 推广上线
     * @throws \Throwable
     */
    public function agentParentShow()
    {
        $request = \YunShop::request();

        $member_info = Member::memberCustomer($request->id)->first();

        if (empty($member_info)) {
            return $this->message('会员不存在', '', 'error');
        }

        $list = MemberParent::parent($request)->orderBy('level', 'asc')->paginate($this->pageSize)->toArray();

        $pager = PaginationHelper::show($list['total'], $list['current_page'], $this->pageSize);

        return $this->successJson('ok', [
            'member' => $member_info,
            'list' => (new FansItemService())->setParentFansItem($list),
            'pager' => $pager,
            'total' => $list['total'],
            'request' => $request
        ]);

    }

    /**
     * 推广上线导出
     */
    public function agentParentExport()
    {
        if (!$this->checkVerify()) {
            return $this->message('二次校验失败','','error');
        }

        $file_name = date('Ymdhis', time()) . '会员上级导出';
        $export_data[0] = ['ID', '昵称', '真实姓名', '电话'];
        $member_id = request()->id;
        $export_page = request()->export_page ? request()->export_page : 1;
        $child = MemberParent::where('member_id', $member_id)->with(['hasOneMember']);
        $export_model = new ExportService($child, $export_page);

        foreach ($child->get() as $key => $item) {
            $member = $item->hasOneMember;
            $export_data[$key + 1] = [
                $member->uid,
                changeSpecialSymbols($member->nickname),
                $member->realname,
                $member->mobile,
            ];
        }

        // 此处参照商城订单管理的导出接口
        app('excel')->store(new \app\exports\FromArray($export_data), $file_name . '.xlsx', 'export');
        app('excel')->download(new \app\exports\FromArray($export_data), $file_name . '.xlsx')->send();
    }

    public function firstAgentExport()
    {
        if (!$this->checkVerify()) {
            return $this->message('二次校验失败','','error');
        }

        $export_data = [];
        $file_name = date('Ymdhis', time()) . '会员直推上级导出';

        $member_id = request()->id;
        $export_page = request()->export_page ? request()->export_page : 1;
        $levelId = [];
        if (app('plugins')->isEnabled('team-dividend')) {
            $team_list = TeamDividendLevelModel::getList()->get();
            foreach ($team_list as $level) {
                $export_data[0][] = $level->level_name;
                $levelId[] = $level->id;
            }
        }

        array_push($export_data[0], '会员ID', '会员', '姓名/手机号码');

        $child = MemberParent::where('member_id', $member_id)
            ->where('level', 1)
            ->with(['hasManyParent' => function ($q) {
                $q->orderBy('level', 'asc');
            }])
            ->get();

        foreach ($child as $key => $item) {

            $level = $this->getLevel($item, $levelId);

            $export_data[$key + 1] = $level;

            array_push($export_data[$key + 1],
                $item->member_id,
                $item->hasOneMember->nickname,
                $item->hasOneMember->realname . '/' . $item->hasOneMember->mobile);
        }
        // 此处参照商城订单管理的导出接口
        app('excel')->store(new \app\exports\FromArray($export_data), $file_name . '.xlsx', 'export');
        app('excel')->download(new \app\exports\FromArray($export_data), $file_name . '.xlsx')->send();
    }

    public function getLevel($member, $levelId)
    {
        $data = [];
//        $num = count($member->hasManyParentTeam);
        foreach ($levelId as $k => $value) {
            foreach ($member->hasManyParent as $key => $parent) {
                if ($parent->hasOneTeamDividend->hasOneLevel->id == $value) {
                    $data[$k] = $parent->hasOneMember->nickname . ' ' . $parent->hasOneMember->realname . ' ' . $parent->hasOneMember->mobile;
                    break;
                }
            }
            $data[$k] = $data[$k] ?: '';
        }

        return $data;
    }

    /**
     * 数据导出
     *
     */
    public function export()
    {
        if (!$this->checkVerify()) {
            return $this->message('二次校验失败','','error');
        }
        $parames = \YunShop::request();
        $member_builder = Member::searchNewMembers($parames);
        $export_page = request()->export_page ? request()->export_page : 1;
        $export_model = new ExportService($member_builder, $export_page);
        $handle_nickname = new HandleNickname();

        $file_name = date('Ymdhis', time()) . '会员导出' . $export_page;

        $export_data[0] = ['会员ID', '推荐人', '推荐人ID', '推荐人手机号', '粉丝', '姓名', '手机号', '等级', '分组', '注册时间', '积分', '余额', '订单', '金额', '关注', '提现手机号'];

        $export_model_data = $export_model->builder_model;
        if (empty($export_model_data)) exit;

        if ($parames['search']['first_count'] ||
            $parames['search']['second_count'] ||
            $parames['search']['third_count'] ||
            $parames['search']['team_count']
        ) {
            $result_ids = [];
            if ($parames['search']['first_count']) {
                $result_ids = $this->getChildCount($parames['search']['first_count'], 1);
            }
            if ($parames['search']['second_count']) {
                $second_ids = $this->getChildCount($parames['search']['second_count'], 2);
                $result_ids = empty($result_ids) ? $second_ids : array_intersect($result_ids, $second_ids);
                unset($second_ids);
            }
            if ($parames['search']['third_count']) {
                $third_ids = $this->getChildCount($parames['search']['third_count'], 3);
                $result_ids = empty($result_ids) ? $third_ids : array_intersect($result_ids, $third_ids);
                unset($third_ids);
            }
            if ($parames['search']['team_count']) {
                $team_ids = $this->getChildCount($parames['search']['team_count']);
                $result_ids = empty($result_ids) ? $team_ids : array_intersect($result_ids, $team_ids);
                unset($team_ids);
            }

            $export_model_data = $export_model_data->whereIn('uid', $result_ids);
        }

        foreach ($export_model_data->toArray() as $key => $item) {
            if (!empty($item['yz_member']) && !empty($item['yz_member']['agent'])) {
                $agent = $handle_nickname->removeEmoji($item['yz_member']['agent']['nickname']);

            } else {
                $agent = '总店';
            }
            if (!empty($item['yz_member']) && !empty($item['yz_member']['group'])) {
                $group = $item['yz_member']['group']['group_name'];

            } else {
                $group = '';
            }

            if (!empty($item['yz_member']) && !empty($item['yz_member']['level'])) {
                $level = $item['yz_member']['level']['level_name'];

            } else {
                $level = '';
            }

            $order = $item['has_one_order']['total'] ?: 0;
            $price = $item['has_one_order']['sum'] ?: 0;

            if (!empty($item['has_one_fans'])) {
                if ($item['has_one_fans']['followed'] == 1) {
                    $fans = '已关注';
                } else {
                    $fans = '未关注';
                }
            } else {
                $fans = '未关注';
            }
            if (substr($item['nickname'], 0, strlen('=')) === '=') {
                $item['nickname'] = '，' . $handle_nickname->removeEmoji($item['nickname']);
            }

            if (!$item['yz_member']['parent_id']) {
                $parent_id = 0;
            } else {
                $parent_id = $item['yz_member']['parent_id'];
            }

            if (!$item['yz_member']['agent'] || !$item['yz_member']['agent']['mobile']) {
                $parent_mobile = '';
            } else {
                $parent_mobile = $item['yz_member']['agent']['mobile'];
            }

            $export_data[$key + 1] = [$item['uid'], $agent, $parent_id, $parent_mobile, $item['nickname'], $handle_nickname->removeEmoji($item['realname']), $item['mobile'],
                $level, $group, date('Y-m-d H:i:s', $item['createtime']), $item['credit1'], $item['credit2'], $order,
                $price, $fans, $item['yz_member']['withdraw_mobile']];
        }

        $export_model->export($file_name, $export_data, $this->exportRoute);
    }


    public function search_member()
    {
        $members = [];
        $parent_id = \YunShop::request()->parent;

        if (is_numeric($parent_id)) {
            $member = Member::getMemberById($parent_id);

            if (!is_null($member)) {
                $members[] = $member->toArray();
            }

            if (0 == $parent_id) {
                $members = 0;
            }
        }
        return $this->successJson('ok', ['members' => $members]);
    }

    //查找用户
    //Todo 后台优化（原方法search_member,其他方法可能用到，暂未删除）
    public function searchMember()
    {
        $members = [];
        $parent_id = request()->parent;

        if (is_numeric($parent_id)) {
            $member = Member::getMemberById($parent_id);

            if (!is_null($member)) {
                $members[] = $member->toArray();
            }

            if ($parent_id == 0) {
                //返回总店
                $members[] = ['uid' => 0, 'nickname' => '总店'];
            }
        }

        return $this->successJson('ok', [
            'members' => $members
        ]);
    }

    //修改会员上线
    public function change_relation_back()
    {
        $parent_id = request()->parent;
        $uid = request()->member;

        $msg = MemberShopInfo::change_relation($uid, $parent_id);

        switch ($msg['status']) {
            case -1:
                return $this->errorJson('上线没有推广权限');
                break;
            case 1:
                return $this->successJson('修改成功');
                break;
            case 0:

            default:
                return $this->errorJson('修改失败');
        }
    }

    public function change_relation()
    {
        $parent_id = (int)request()->parent;
        $uid = (int)request()->member;
        $member = SubMemberModel::getMemberShopInfo($uid);
        //判断修改的上级是否推广员
        if ($parent_id != 0) {
            $parent = SubMemberModel::getMemberShopInfo($parent_id);
            if (!($parent->is_agent == 1 && $parent->status == 2)) {
                return $this->errorJson('上线没有推广权限');
            }
            if ($parent->parent_id == $uid) {
                return $this->errorJson('会员上下线冲突');
            }
            //验证是否闭环关系链
            $chain = ParentOfMember::where('member_id', $parent_id)->pluck('parent_id');
            $chain->push($uid);
            $chain->push($parent_id);
            if ($chain->count() != $chain->unique()->count()) {
                return $this->errorJson('关系链闭环，请检测关系链');
            }
        }
        $record_data = [
            'uid' => $uid,
            'parent_id' => $member->parent_id,
            'after_parent_id' => $parent_id,
            'status' => 0,
            'uniacid' => \YunShop::app()->uniacid
        ];
        $member_record = MemberRecord::create($record_data);
        $this->dispatch(new ModifyRelationshipChainJob($uid, $parent_id, $member_record->id, \YunShop::app()->uniacid));
        return $this->successJson('关系链修改请求成功，请到关系链修改记录查看修改结果');
    }

    //会员上线修改记录
    public function member_record()
    {
        $records = MemberRecord::getRecord(request()->uid);

        return $this->successJson('ok', [
            'records' => $records
        ]);
    }

    public function updateWechatOpenData()
    {
        $status = \YunShop::request()->status;

        if (Cache::has('queque_wechat_total')) {
            Cache::forget('queque_wechat_total');
        }

        if (Cache::has('queque_wechat_page')) {
            Cache::forget('queque_wechat_page');
        }

        if (is_null($status)) {
            $pageSize = 100;

            $member_info = Member::getQueueAllMembersInfo(\YunShop::app()->uniacid);

            $total = $member_info->count();
            $total_page = ceil($total / $pageSize);

            \Log::debug('------total-----', $total);
            \Log::debug('------total_page-----', $total_page);

            Cache::put('queque_wechat_total', $total_page, 30);

            for ($curr_page = 1; $curr_page <= $total_page; $curr_page++) {
                \Log::debug('------curr_page-----', $curr_page);
                $offset = ($curr_page - 1) * $pageSize;
                $member_info = Member::getQueueAllMembersInfo(\YunShop::app()->uniacid, $pageSize, $offset)->get();
                \Log::debug('------member_count-----', $member_info->count());

                $job = (new \app\Jobs\wechatUnionidJob(\YunShop::app()->uniacid, $member_info));
                dispatch($job);
            }
        } else {
            switch ($status) {
                case 0:
                    return $this->message('微信开放平台数据同步失败', yzWebUrl('member.member.index'), 'error');

                    break;
                case 1:
                    return $this->message('微信开放平台数据同步完成', yzWebUrl('member.member.index'));
                    break;
            }
        }

        return view('member.update-wechat', [])->render();
    }

    public function updateWechatData()
    {
        $total = Cache::get('queque_wechat_total') ?: 0;
        $page = Cache::get('queque_wechat_page') ?: 0;
        \Log::debug('--------ajax total-------', $total);
        \Log::debug('--------ajax page-------', $page);
        if ($total == $page) {
            return json_encode(['status' => 1]);
        } else {
            return json_encode(['status' => 0]);
        }
    }

    public function exportRelation()
    {
        ini_set('memory_limit',-1);
        $page = request()->input('page')??0;
        $del_page = request()->input('del_page')??0;
        $del_total = request()->input('del_total')??MemberParent::uniacid()->count();
        $max_parent_id = request()->input('max_parent_id')??MemberParent::uniacid()->orderBy('id','desc')->value('id')??0;
        $max_child_id = request()->input('max_child_id')??MemberChildren::uniacid()->orderBy('id','desc')->value('id')??0;
        $max_member_id = request()->input('max_member_id')??\app\common\models\MemberShopInfo::uniacid()->orderBy('member_id','desc')->value('member_id');
        if ($page > 0) {
            $parent_list = \app\common\models\MemberShopInfo::uniacid()->where('member_id','<=',$max_member_id)->pluck('parent_id','member_id')->toArray();
            $count = count($parent_list);
            $total_page = ceil($count/5000);
            $parents = array_chunk($parent_list,5000,true)[$page-1];
            foreach ($parents as $member_id => $parent_id) {
                $current_parent_id = $parent_id;
                $level = 1;
                $exist_parent = [];
                while ($current_parent_id != 0) {
                    if (in_array($current_parent_id,$exist_parent)) {
                        return $this->errorJson('会员id:'.$member_id.'关系链闭环,请检查关系链');
                    }
                    $exist_parent[] = $current_parent_id;
                    $member_parent[] = [
                        'member_id' => $member_id,
                        'parent_id' => $current_parent_id,
                        'level' => $level,
                        'uniacid' => \YunShop::app()->uniacid,
                    ];
                    $member_child[] = [
                        'member_id' => $current_parent_id,
                        'child_id' => $member_id,
                        'level' => $level,
                        'uniacid' => \YunShop::app()->uniacid,
                    ];
                    $current_parent_id = $parent_list[$current_parent_id];
                    $level++;
                }
            }
            foreach (array_chunk($member_parent,10000) as $value) {
                MemberParent::insert($value);
            }
            foreach (array_chunk($member_child,10000) as $value) {
                \app\common\models\member\MemberChildren::insert($value);
            }
            unset($member_parent);
            unset($member_child);
            return $this->successJson('',['page'=>$page + 1,
                'process'=> $page / $total_page * 100,
                'del_process'=>100,
                'del_total' => $del_total,
                'del_page'  => $del_page,
                'max_parent_id'  => $max_parent_id,
                'max_child_id'  => $max_child_id,
                'max_member_id'  => $max_member_id,
                'status'=>empty($parents)]);
        } else {
            $parent_del = MemberParent::uniacid()->when($max_parent_id,function($query) use ($max_parent_id) {
                return $query->where('id','<=',$max_parent_id);
            })->limit(10000)->delete();
            $child_del = \app\common\models\member\MemberChildren::uniacid()->when($max_child_id,function($query) use ($max_child_id) {
                return $query->where('id','<=',$max_child_id);
            })->limit(10000)->delete();
            $del_process = 100;
            if ($del_total > 0) {
                $del_process = $del_page * 10000 / $del_total * 100;
            }
            if ($parent_del == 0 && $child_del == 0) {
                $page = 1;
                $del_process = 100;
            }

            return $this->successJson('',[
                'page'=>$page,
                'process'=> 0,
                'del_process'=> $del_process,
                'del_total' => $del_total,
                'del_page'  => $del_page + 1,
                'max_parent_id'  => $max_parent_id,
                'max_child_id'  => $max_child_id,
                'max_member_id'  => $max_member_id,
            ]);
        }
    }



    public function changeMemberRelationJob()
    {
        $member_id = \YunShop::request()->uid;
        $parent_id = \YunShop::request()->pid;

        $job = new ChangeMemberRelationJob($member_id, $parent_id);
        dispatch($job);
    }

    public function changeMobile()
    {
        $uid = request()->uid;
        $mobile = request()->mobile;

        if (!$mobile) {
            return $this->errorJson('请输入手机号码');
        }

        $mobile_member = Member::uniacid()->where('mobile', $mobile)->first();
        if ($mobile_member) {
            return $this->errorJson('该手机已被绑定');
        }

        $mc_member = Member::uniacid()->where('uid', $uid)->first();
        if ($mc_member) {
            //添加修改日志
            $data = [
                'member_id' => $uid,
                'uniacid' => \YunShop::app()->uniacid,
                'mobile_before' => $mc_member->mobile,
                'mobile_after' => $mobile,
                'created_at' => time(),
            ];
            MemberChangeMobileLog::insert($data);

            $mc_member->mobile = $mobile;
            if ($mc_member->save()) {
                return $this->successJson('绑定成功');
            } else {
                return $this->errorJson('绑定失败');
            }
        } else {
            return $this->errorJson('会员不存在');
        }
    }

    public function changeMobileLog()
    {
        $uid = request()->uid;
        $list = MemberChangeMobileLog::uniacid()->where('member_id', $uid)->get()->toArray();

        return $this->successJson('ok', [
            'list' => $list,
        ]);
    }

    public function memberMerge()
    {
        $member_id = request()->uid;
        $yz_member = MemberShopInfo::getMemberShopInfo($member_id);
        $mark_member_id = $yz_member->mark_member_id;
        $relation_set = Setting::get('relation_base');
        $exception = DB::transaction(function () use ($yz_member, $relation_set) {
            $abandon_member_id = $yz_member->mark_member_id;
            $main_member_id = $yz_member->member_id;
            $abandon_member = Member::getMemberById($abandon_member_id);
            $main_member = Member::getMemberById($main_member_id);
            $merge_data = [
                'uniacid' => \YunShop::app()->uniacid,
                'before_uid' => $abandon_member_id,
                'after_uid' => $main_member_id,
                'before_mobile' => $abandon_member->mobile,
                'after_mobile' => $main_member->mobile,
                'before_point' => $abandon_member->credit1?:0.00,
                'after_point' => bcadd($main_member->credit1, $abandon_member->credit1, 2)?:0.00,
                'before_amount' => $abandon_member->credit2?:0.00,
                'after_amount' => bcadd($main_member->credit2, $abandon_member->credit2, 2)?:0.00,
                'set_content' => json_encode($relation_set),
                'merge_type' => 2,
            ];
            //合并处理服务
            (new MemberMergeService($main_member_id, $abandon_member_id, $merge_data))->handel();
            //小程序
            MemberMiniAppModel::where('member_id', $yz_member->mark_member_id)->update(['member_id' => $yz_member->member_id]);
            //公众号
            McMappingFans::where('uid', $yz_member->mark_member_id)->update(['uid' => $yz_member->member_id]);
            //app
            \app\frontend\modules\member\models\MemberWechatModel::where('member_id', $yz_member->mark_member_id)->update(['member_id' => $yz_member->member_id]);
            //聚合cps
            if (Schema::hasTable('yz_member_aggregation_app')) {
                DB::table('yz_member_aggregation_app')->where('member_id', $yz_member->mark_member_id)->update(['member_id' => $yz_member->member_id]);
            }
            //企业微信
            if (Schema::hasTable('yz_member_customer')) {
                DB::table('yz_member_customer')->where('uid',$yz_member->mark_member_id)->update(['uid' => $yz_member->member_id]);
            }
            //统一
            MemberUnique::where('member_id', $yz_member->mark_member_id)->update(['member_id' => $yz_member->member_id]);
            //支付宝
            MemberAlipay::where('member_id', $yz_member->mark_member_id)->update(['member_id' => $yz_member->member_id]);
            Member::where('uid', $yz_member->mark_member_id)->delete();  //删除mc_members数据
            MemberShopInfo::where('member_id', $yz_member->mark_member_id)->delete();  //软删除yz_member
            $mobile = $main_member->mobile ?: $abandon_member->mobile;
            Member::where('uid', $main_member_id)->update([
                'mobile' => $mobile
            ]);
            $yz_member->is_old = 0;
            $yz_member->mark_member_id = 0;
            $yz_member->save();
        });
        if (!is_null($exception)) {
            return $this->errorJson('合并失败');
        }
        event(new MergeMemberEvent($member_id, $mark_member_id));
        return $this->successJson('合并成功');
    }

    public function mergeMember()
    {
        $post = request()->post();
        if (empty($post) || !$post['hold_member_id'] || !$post['give_up_member_id']) {
            return $this->errorJson('请传入完整参数');
        }
        $main_member_id = $post['hold_member_id'];
        $abandon_member_id = $post['give_up_member_id'];
        try {
            $this->validateMember($main_member_id, $abandon_member_id);
        } catch (ShopException $exception) {
            return $this->errorJson($exception->getMessage());
        }
        $relation_set = Setting::get('relation_base');
        $exception = DB::transaction(function () use ($main_member_id, $abandon_member_id, $relation_set) {
            $abandon_member = Member::getMemberById($abandon_member_id);
            $main_member = Member::getMemberById($main_member_id);
            $merge_data = [
                'uniacid' => \YunShop::app()->uniacid,
                'before_uid' => $abandon_member_id,
                'after_uid' => $main_member_id,
                'before_mobile' => $abandon_member->mobile,
                'after_mobile' => $main_member->mobile,
                'before_point' => $abandon_member->credit1?:0.00,
                'after_point' => bcadd($main_member->credit1, $abandon_member->credit1, 2)?:0.00,
                'before_amount' => $abandon_member->credit2?:0.00,
                'after_amount' => bcadd($main_member->credit2, $abandon_member->credit2, 2)?:0.00,
                'set_content' => json_encode($relation_set),
                'merge_type' => 4,
            ];
            //合并处理服务
            (new MemberMergeService($main_member_id, $abandon_member_id, $merge_data))->handel();
            //小程序
            MemberMiniAppModel::where('member_id', $abandon_member_id)->update(['member_id' => $main_member_id]);
            //公众号
            McMappingFans::where('uid', $abandon_member_id)->update(['uid' => $main_member_id]);
            //app
            \app\frontend\modules\member\models\MemberWechatModel::where('member_id', $abandon_member_id)->update(['member_id' => $main_member_id]);
            //聚合cps
            if (Schema::hasTable('yz_member_aggregation_app')) {
                DB::table('yz_member_aggregation_app')->where('member_id', $abandon_member_id)->update(['member_id' => $main_member_id]);
            }
            //企业微信
            if (Schema::hasTable('yz_member_customer')) {
                DB::table('yz_member_customer')->where('uid',$abandon_member_id)->update(['uid' => $main_member_id]);
            }
            //统一
            MemberUnique::where('member_id', $abandon_member_id)->update(['member_id' => $main_member_id]);
            //支付宝
            MemberAlipay::where('member_id', $abandon_member_id)->update(['member_id' => $main_member_id]);
            Member::where('uid', $abandon_member_id)->delete();  //删除mc_members数据
            MemberShopInfo::where('member_id', $abandon_member_id)->delete();  //软删除yz_member
            $mobile = $main_member->mobile ?: $abandon_member->mobile;
            Member::where('uid', $main_member_id)->update([
                'mobile' => $mobile
            ]);
        });
        if (!is_null($exception)) {
            return $this->errorJson('合并失败');
        }
        event(new MergeMemberEvent($main_member_id, $abandon_member_id));
        return $this->successJson('合并成功');
    }

    private function validateMember($hold_member_id, $give_up_member_id)
    {
        $hold_member = Member::getMemberById($hold_member_id);
        $give_up_member = Member::getMemberById($give_up_member_id);
        if (!$hold_member || !$give_up_member) {
            throw new ShopException('请确认所填会员id是否正确');
        }
        if ($hold_member->mobile && $give_up_member->mobile) {
            throw new ShopException('所选两个会员都绑定了手机号，不能进行合并');
        }
        //微信生态
        //公众号
        $hold_fans = McMappingFans::getFansById($hold_member_id);
        $give_up_fans = McMappingFans::getFansById($give_up_member_id);
        //小程序
        $hole_mini = MemberMiniAppModel::getFansById($hold_member_id);
        $give_up_mini = MemberMiniAppModel::getFansById($give_up_member_id);
        //app
        $hold_app = \app\frontend\modules\member\models\MemberWechatModel::getFansById($hold_member_id);
        $give_up_app = \app\frontend\modules\member\models\MemberWechatModel::getFansById($give_up_member_id);
        //聚合cps
        if (Schema::hasTable('yz_member_aggregation_app')) {
            $hold_cps = DB::table('yz_member_aggregation_app')->where('member_id', $hold_member_id)->first();
            $give_up_cps = DB::table('yz_member_aggregation_app')->where('member_id', $give_up_member_id)->first();
        }
        //统一
        $hold_uni = MemberUnique::where('member_id', $hold_member_id)->first();
        $give_up_uni = MemberUnique::where('member_id', $give_up_member_id)->first();
        if (($hole_mini||$hold_fans||$hold_app||$hold_cps||$hold_uni) && ($give_up_fans||$give_up_mini||$give_up_app||$give_up_cps||$give_up_uni)) {
            throw new ShopException('同微信生态会员不能合并');
        }
        //支付宝
        $hold_ali = MemberAlipay::where('member_id', $hold_member_id)->first();
        $give_up_ali = MemberAlipay::where('member_id', $give_up_member_id)->first();
        if ($hold_ali && $give_up_ali) {
            throw new ShopException('同支付宝生态会员不能合并');
        }
    }

    private function changeYunSignData($hold_uid, $give_up_uid)
    {
        $yunSignTableArr = [
            'yz_yun_sign_company_account', 'yz_yun_sign_contract', 'yz_yun_sign_contract_cc', 'yz_yun_sign_contract_log',
            'yz_yun_sign_contract_num', 'yz_yun_sign_contract_role', 'yz_yun_sign_contract_template', 'yz_yun_sign_order',
            'yz_yun_sign_person_account', 'yz_yun_sign_person_seal', 'yz_yun_sign_short_url', 'yz_yun_sign_worker',
            'yz_yun_sign_apps', 'yz_yun_sign_contract_recharge'
        ];
        $yunSignApiTableArr = ['yz_yun_sign_api_contract'];
        $shopSignTableArr = [
            'yz_shop_esign_company_account', 'yz_shop_esign_contract', 'yz_shop_esign_contract_role', 'yz_shop_esign_contract_template',
            'yz_shop_esign_order', 'yz_shop_esign_person_account',
        ];
        if (\YunShop::plugin()->get('yun-sign')) {
            $column = 'uid';
            foreach ($yunSignTableArr as $item) {
                if ($item == 'yz_yun_sign_apps') {
                    $column = 'boss_uid';
                }
                if ($item == 'yz_yun_sign_contract_recharge') {
                    $column = 'member_id';
                }
                $this->updateData($item, $column, $hold_uid, $give_up_uid);
            }
            $uniacid = \YunShop::app()->uniacid;
            $tablePrefix = DB::getTablePrefix();
            DB::raw('UPDATE ' . $tablePrefix . 'yz_yun_sign_contract_num_log' . ' SET ' . $column . '=' . $hold_uid . ' where boss_uid =' . $give_up_uid . ' and uniacid=' . $uniacid);
        }
        if (\YunShop::plugin()->get('yun-sign-api')) {
            foreach ($yunSignApiTableArr as $item) {
                $this->updateData($item, 'uid', $hold_uid, $give_up_uid);
            }
        }
        if (\YunShop::plugin()->get('shop-esign')) {
            foreach ($shopSignTableArr as $item) {
                $this->updateData($item, 'uid', $hold_uid, $give_up_uid);
            }
        }
    }

    private function updateData($table, $column, $hold_uid, $give_up_uid)
    {
        $uniacid = \YunShop::app()->uniacid;
        $tablePrefix = DB::getTablePrefix();
        $sql = 'UPDATE ' . $tablePrefix . $table . ' SET ' . $column . '=' . $hold_uid . ' where boss_uid =' . $give_up_uid . ' and uniacid=' . $uniacid;

        return DB::raw($sql);
    }

    public function memberChart()
    {
        $type = request()->chart_type;

        $count = 0;

        switch ($type) {
            case 5 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->where('mc_members.mobile', '!=', '')
                    ->count();
                break;
            case 1 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->join('mc_mapping_fans', 'mc_members.uid', '=', 'mc_mapping_fans.uid')
                    ->where('mc_mapping_fans.uniacid', \YunShop::app()->uniacid)
                    ->count();
                break;
            case 2 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->join('yz_member_mini_app', 'mc_members.uid', '=', 'yz_member_mini_app.member_id')
                    ->where('yz_member_mini_app.uniacid', \YunShop::app()->uniacid)
                    ->count();
                break;
            case 3 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->join('yz_member_wechat', 'mc_members.uid', '=', 'yz_member_wechat.member_id')
                    ->where('yz_member_wechat.uniacid', \YunShop::app()->uniacid)
                    ->count();
                break;
            case 4 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->join('yz_member_unique', 'mc_members.uid', '=', 'yz_member_unique.member_id')
                    ->where('yz_member_unique.uniacid', \YunShop::app()->uniacid)
                    ->count();
                break;
            case 6 :
                if (!app('plugins')->isEnabled('wechat-customers')) {
                    $count = 0;
                    break;
                }
                $prefix = DB::getTablePrefix();
                $res = DB::select("
                    SELECT
    COUNT(1) AS total
FROM
    (
    SELECT
        m.uid
    FROM
        {$prefix}yz_member_customer AS c
    JOIN {$prefix}mc_members AS m
    ON
        c.uid = m.uid
    JOIN {$prefix}yz_member AS yz
    ON
        c.uid = yz.member_id
    WHERE
        yz.deleted_at IS NULL
    GROUP BY
        m.uid
) AS temp
                ");
                $count = $res[0]['total'];
                break;
        }

        return $this->successJson('ok', [
            'count' => $count,
        ]);
    }

    public function recordList()
    {
        return view('member.record-list', [])->render();
    }

    public function recordDatas()
    {
        $list = MemberRecord::select(['id', 'uid', 'parent_id', 'after_parent_id', 'status', 'created_at'])->uniacid()->orderBy('id', 'desc')->paginate(20)->toArray();
        return $this->successJson('ok', ['list' => $list]);
    }

    public function changeLevel()  //修改会员等级
    {
        $level_id = request()->level_id;
        $uid = request()->uid;

        if (!$uid) {
            return $this->errorJson('参数错误');
        }
        $member = \app\common\models\MemberShopInfo::uniacid()->where('member_id', $uid)->first();

        $member->level_id = $level_id;
        if ($member->save()) {
            return $this->successJson('会员等级修改成功');
        } else {
            return $this->error('失败');
        }

    }

    public function changeGroup()  //修改会员分组
    {
        $group_id = request()->group_id;
        $uid = request()->uid;

        if (!$uid) {
            return $this->errorJson('参数错误');
        }
        $member = \app\common\models\MemberShopInfo::uniacid()->where('member_id', $uid)->first();

        $member->group_id = $group_id;
        if ($member->save()) {
            return $this->successJson('会员分组修改成功');
        } else {
            return $this->error('失败');
        }

    }

}
