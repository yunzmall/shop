<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
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
use app\backend\modules\member\services\MemberServices;
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
use app\common\models\member\ChildrenOfMember;
use app\common\models\member\MemberChangeMobileLog;
use app\common\models\member\MemberMergeLog;
use app\common\models\member\ParentOfMember;
use app\common\models\MemberAlipay;
use app\common\models\MemberGroup as Member_Group;
use app\common\models\MemberMiniAppModel;
use app\common\models\MemberShopInfo as MemberShop_Info;
use app\common\models\MemberWechatModel;
use app\common\services\ExportService;
use app\common\services\member\MemberRelation;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\SubMemberModel as SubMember_Model;
use app\Jobs\ChangeMemberRelationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use iscms\AlismsSdk\RequestCheckUtil;
use Yunshop\TeamDividend\models\TeamDividendLevelModel;

//use app\frontend\modules\member\models\SubMemberModel;


class MemberController extends BaseController
{
    private $pageSize = 20;
    protected $publicAction = ['addMember'];
    protected $ignoreAction = ['addMember'];
    /**
     * 列表
     *
     */
    public function index()
    {
        $groups = MemberGroup::getMemberGroupList();
        $levels = MemberLevel::getMemberLevelList();

        $uniacid = \YunShop::app()->uniacid;

        $parames = \YunShop::request();

        if (strpos($parames['search']['searchtime'], '×') !== FALSE) {
            $search_time = explode('×', $parames['search']['searchtime']);

            if (!empty($search_time)) {
                $parames['search']['searchtime'] = $search_time[0];

                $start_time = explode('=', $search_time[1]);
                $end_time = explode('=', $search_time[2]);

                $parames->times = [
                    'start' => $start_time[1],
                    'end' => $end_time[1]
                ];
            }
        }

        $list = Member::searchMembers($parames);

        if ($parames['search']['first_count'] ||
            $parames['search']['second_count'] ||
            $parames['search']['third_count'] ||
            $parames['search']['team_count']
        ) {

            //------------------new-------------------
            $result_ids =  [];
            if ($parames['search']['first_count']) {
                $result_ids = $this->getChildCount($parames['search']['first_count'],1);
            }
            if ($parames['search']['second_count']) {
                $second_ids = $this->getChildCount($parames['search']['second_count'],2);
                $result_ids = empty($result_ids)?$second_ids:array_intersect($result_ids,$second_ids);
                unset($second_ids);
            }
            if ($parames['search']['third_count']) {
                $third_ids = $this->getChildCount($parames['search']['third_count'],3);
                $result_ids = empty($result_ids)?$third_ids:array_intersect($result_ids,$third_ids);
                unset($third_ids);
            }
            if ($parames['search']['team_count']) {
                $team_ids = $this->getChildCount($parames['search']['team_count']);
                $result_ids = empty($result_ids)?$team_ids:array_intersect($result_ids,$team_ids);
                unset($team_ids);
            }
            //------------------new-------------------

            /*
            //set_time_limit(0);
            $member_ids = MemberShopInfo::uniacid()->select('member_id')->get();

            $result_ids = [];
            foreach ($member_ids as $key => $member) {

                $is_added = true;
                if ($parames['search']['first_count']) {
                    $first_count = $this->getMembersLower($member->member_id,1);
                    $result_ids = $this->getResultIds($result_ids,$member->member_id,$parames['search']['first_count'],$first_count,$is_added);
                    $is_added = false;
                }
                if ($parames['search']['second_count']) {

                    $second_count = $this->getMembersLower($member->member_id,2);
                    $result_ids = $this->getResultIds($result_ids,$member->member_id,$parames['search']['second_count'],$second_count,$is_added);
                    $is_added = false;
                }
                if ($parames['search']['third_count']) {
                    $third_count = $this->getMembersLower($member->member_id,3);
                    $result_ids = $this->getResultIds($result_ids,$member->member_id,$parames['search']['third_count'],$third_count,$is_added);
                    $is_added = false;
                }
                if ($parames['search']['team_count']) {
                    $team_count = $this->getMemberTeam($member->member_id);
                    $result_ids = $this->getResultIds($result_ids,$member->member_id,$parames['search']['team_count'],$team_count,$is_added);
                }

            }
            */
            $list = $list->whereIn('uid', $result_ids);
        }

        $list = $list->orderBy('uid', 'desc')
            ->paginate($this->pageSize)
            ->toArray();

        $set = \Setting::get('shop.member');

        if (empty($set['level_name'])) {
            $set['level_name'] = '普通会员';
        }

        $pager = PaginationHelper::show($list['total'], $list['current_page'], $this->pageSize);

        $starttime = strtotime('-1 month');
        $endtime = time();
        if (isset($parames['search']['searchtime']) &&  $parames['search']['searchtime'] == 1) {
            if ($parames['search']['times']['start'] != '请选择' && $parames['search']['times']['end'] != '请选择') {
                $starttime = strtotime($parames['search']['times']['start']);
                $endtime = strtotime($parames['search']['times']['end']);
            }
        }

        return view('member.index', [
            'list' => $list,
            'levels' => $levels,
            'groups' => $groups,
            'endtime' => $endtime,
            'starttime' => $starttime,
            'total' => $list['current_page'] <= $list['last_page'] ? $list['total'] : 0,
            'pager' => $pager,
            'request' => \YunShop::request(),
            'set' => $set,
            'opencommission' => 1
        ])->render();
    }

    /**
     * 获取$level级下线人数超过$countNum的会员id
     * @param int $countNum
     * @param string $level
     * @return array
     */
    public function getChildCount($countNum = 0,$level = '')
    {
        $model = MemberChildren::uniacid()->select(DB::raw('member_id,COUNT(*) as count_num'));
        if(!empty($level))
        {
            $model->where('level',$level);
        }
        $ids = $model->groupBy('member_id')->having('count_num','>=',$countNum)
            ->get()->toArray();
        return array_column($ids,'member_id');
    }

//会员下线导出
    public function doExport(){

        $export_page = request()->export_page ? request()->export_page : 1;
        //清除之前没有导出的文件
        if ($export_page == 1){

            $fileNameArr = file_tree(storage_path('exports'));
            foreach ($fileNameArr as $val ) {
                if(file_exists(storage_path('exports/' . basename($val)))){
                    unlink(storage_path('exports/') . basename($val)); // 路径+文件名称
                }
            }
        }
        $request = \YunShop::request();
        $member_info = Member::getUserInfos($request->id)->first();
        if (empty($member_info)) {
            return $this->message('会员不存在','', 'error');
        }
        $list=Member::getAgentInfoByMemberId($request);
        if ($list->get()->isEmpty()) {
            return $this->message('订单导出数据为空','', 'error');
        }
        $export_model = new ExportService($list, $export_page);
        if (!$export_model->builder_model->isEmpty()) {
            $file_name = date('Ymdhis', time()) . '订单导出';//返现记录导出
            $export_data[0] = ['会员ID', '推荐人','粉丝', '姓名', '手机号码','状态','下线状态','注册时间','关注'];
            $status_name=[
                0=>"未审核",
                1=>"审核中",
                2=>"已审核"
            ];
            $inviter_name=[
                0=>"暂定下线",
                1=>"锁定关系下线"
            ];
            foreach ($export_model->builder_model->toArray() as $key => $value) {
                if(empty($value['has_one_fans']['followed'])){
                    if(empty($value['has_one_fans']['uid'])){
                        $follow="未关注";
                    }else{
                        $follow="取消关注";
                    }
                }else{
                    $follow="已关注";
                }
                $export_data[$key + 1] = [
                    $value['uid'],
                    $value['yz_member']['agent']['nickname'],
                    $value['nickname'],
                    $value['realname'],
                    $value['mobile'],
                    $value['yz_member']['is_agent']==0?"-":$status_name[$value["yz_member"]['status']],
                    $inviter_name[$value['yz_member']['inviter']],
                    date("Y-m-d H:i",$value['createtime']),
                    $follow
                ];
            }
            $export_model->export($file_name, $export_data, 'member.member.agent-old');
        }

    }



    public function addMember(){

        $mobile = \YunShop::request()['mobile'];
        $password = \YunShop::request()['password'];
        $confirm_password = \YunShop::request()['confirm_password'];
        $uniacid = \YunShop::app()->uniacid;
        $member = Setting::get('shop.member');
        //获取图片
        $member_set = \Setting::get('shop.member');
        \Log::info('member_set', $member_set);
        if (isset($member_set) && $member_set['headimg']) {
            $avatar = yz_tomedia($member_set['headimg']);
        } else {
            $avatar = Url::shopUrl('static/images/photo-mr.jpg');
        }

        if ((\Request::getMethod() == 'POST')) {
//            $msg = MemberService::validate($mobile, $password, $confirm_password,'Backstage');
//
//            if ($msg != 1) {
//                return $this->message($msg, yzWebUrl('member.member.add-member'));
//            }

            //判断是否已注册
            $member_info = MemberModel::getId($uniacid, $mobile);
            \Log::info('member_info', $member_info);

            if (!empty($member_info)) {
                throw new AppException('该手机号已被注册');
            }

            //添加mc_members表
            $default_groupid = Member_Group::getDefaultGroupId($uniacid)->first();
            \Log::info('default_groupid', $default_groupid);
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
            \Log::info('salt', $data['salt']);

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
            );

            //添加用户子表
            SubMember_Model::insertData($sub_data);
            //生成分销关系链
            Members::createRealtion($member_id);

//            $cookieid = "__cookie_yun_shop_userid_{$uniacid}";
//            Cookie::queue($cookieid, $member_id);
//            Session::set('member_id', $member_id);

            $password = $data['password'];
            $member_info = MemberModel::getUserInfo($uniacid, $mobile, $password)->first();
            $yz_member = MemberShop_Info::getMemberShopInfo($member_id)->toArray();
            $data = MemberModel::userData($member_info, $yz_member);
            return $this->message("添加用户成功", yzWebUrl('member.member.index'));
        }

        if (empty($member['headimg'])) {
            $val = static_url('resource/images/nopic.jpg');
            $headimg = yz_tomedia($val);
        }else{
            $headimg = yz_tomedia($member['headimg']);
        }
        return view('member.add-member',['img'=>$headimg])->render();
    }

    public function import()
    {
        return view('member.import')->render();
    }

    public function memberExcelDemo()
    {
        $exportData['0'] = ['手机号','密码'];
        \Excel::create('会员批量导入模板', function ($excel) use ($exportData) {
            $excel->setTitle('Office 2005 XLSX Document');
            $excel->setCreator('芸众商城');
            $excel->setLastModifiedBy("芸众商城");
            $excel->setSubject("Office 2005 XLSX Test Document");
            $excel->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.");
            $excel->setKeywords("office 2005 openxml php");
            $excel->setCategory("report file");
            $excel->sheet('info', function ($sheet) use ($exportData) {
                $sheet->rows($exportData);
            });
        })->export('xls');
    }

    public function memberExcel()
    {
        $data = request()->input();
        $uniacid = \YunShop::app()->uniacid;
        //excel 本身就重复的值
        if(!$data['data']['0']['手机号']){
            $this->errorJson('第一项开头必须为手机号');
        }
        if(!$data['data']['0']['密码']){
            $this->errorJson('第二项开头必须为密码');
        }
        $data = array_column($data['data'],null,'手机号');
        foreach ($data as $key => $value) {
            if (!preg_match("/^1[3456789]{1}\d{9}$/", $value['手机号'])) {
                unset($data[$key]);
            }
        }
        $phone = array_keys($data);
        $phones = MemberModel::select('mobile')->where('uniacid', $uniacid)->whereIn('mobile',$phone)->pluck('mobile');
        if(!empty($phones)){
            // 存在重复值 取交集
            $repetPhone = array_intersect($phone,$phones->toArray());
            //删除重复值
            foreach ($repetPhone as $value){
                if(isset($data[$value])){
                    unset($data[$value]);
                }
            }
            if(empty($data)){
                return $this->errorJson('表格所有信息已存在，请勿重复导入!');
            }
        }
        $defaultGroupId = Member_Group::getDefaultGroupId(\YunShop::app()->uniacid)->first();
        //整理数据入库
        $i = 0;
        $array = array();
        //获取图片
        $memberSet = \Setting::get('shop.member');
        \Log::info('member_set',$memberSet);
        if (isset($memberSet) && $memberSet['headimg']) {
            $avatar =yz_tomedia($memberSet['headimg']);
        } else {
            $avatar = Url::shopUrl('static/images/photo-mr.jpg');
        }
        foreach ($data as $v){
            $salt = Str::random(8);
            $array[$i] = [
                'uniacid' => $uniacid,
                'mobile' => $v['手机号'],
                'groupid' => $defaultGroupId->id ?: 0,
                'createtime' => $_SERVER['REQUEST_TIME'],
                'nickname' => $v['手机号'],
                'avatar' => $avatar,
                'gender' => 0,
                'residecity' => '',
                'salt' => $salt,
                'password' => md5($v['密码'].$salt),
            ];
            $i++;
        }
        if(!MemberModel::insert($array)){
            return $this->errorJson('批量添加失败');
        }
        //todo 批量插入同时无法返回主键ID故查询一次
        $idArray = MemberModel::select('uid','mobile')->whereIn('mobile',array_keys($data))->get();
        $defaultSubGroupId = Member_Group::getDefaultGroupId()->first();

        if (!empty($defaultSubGroupId)) {
            $defaultSubGroupId = $defaultSubGroupId->id;
        } else {
            $defaultSubGroupId = 0;
        }

        $subData = [];
        foreach ($idArray as $key => $value){
            $subData[$key] = array(
                'member_id' => $value->uid,
                'uniacid' => $uniacid,
                'group_id' => $defaultSubGroupId,
                'level_id' => 0,
                'invite_code' => \app\frontend\modules\member\models\MemberModel::generateInviteCode(),
                'created_at' => $_SERVER['REQUEST_TIME'],
                'updated_at' => $_SERVER['REQUEST_TIME']
            );
        }

        if (SubMember_Model::insert($subData)){
            return $this->successJson('导入成功');
        }else{
            return $this->errorJson('导入失败');
        }
    }


    private function getResultIds(array $result_ids, $member_id, $compare, $compared, $is_added)
    {
        if ($compare < $compared) {
            ($is_added && !in_array($member_id, $result_ids)) && $result_ids[] = $member_id;
        } else {
            $key = array_search($member_id, $result_ids);
            $key !== false && array_splice($result_ids,$key,1);
        }
        return $result_ids;
    }

    private function getMembersLower($memberId,$level = '')
    {
        $array      = $level ? [$memberId,$level] : [$memberId];
        $condition  = $level ? ' = ?' : '';
        return MemberShopInfo::select('member_id')->whereRaw('FIND_IN_SET(?,relation)' . $condition, $array)->count();
    }

    private function getMemberTeam($memberId)
    {
        $first = MemberShopInfo::select('member_id','parent_id')->where('parent_id',$memberId)->get();

        $result_ids = [];
        if ($first) {
            foreach($first as $key => $member) {
                $result_ids[] = $member->member_id;
                $second = MemberShopInfo::select('member_id','parent_id')->where('parent_id',$member->member_id)->get();
                if ($second) {
                    $ids = $this->getMemberTeamRecursion($second);
                    $result_ids = array_merge($result_ids,$ids);
                }
            }
        }

        return count($result_ids);
    }

    private function getMemberTeamRecursion($memberIds)
    {
        $result_ids = [];
        foreach($memberIds as $key => $member) {
            $result_ids[] = $member->member_id;
            $first = MemberShopInfo::select('member_id','parent_id')->where('parent_id',$member->member_id)->get();
            if ($first) {
                $ids = $this->getMemberTeamRecursion($first);
                $result_ids = array_merge($result_ids,$ids);
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
//        $groups = MemberGroup::getMemberGroupList();
//        $levels = MemberLevel::getMemberLevelList();
//
//        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;
//
//        if ($uid == 0 || !is_int($uid)) {
//            $this->message('参数错误', '', 'error');
//            exit;
//        }
//
//        $member = Member::getMemberInfoBlackById($uid);
//
//        if (!empty($member)) {
//            $member = $member->toArray();
//
//            if (1 == $member['yz_member']['is_agent'] && 2 == $member['yz_member']['status']) {
//                $member['agent'] = 1;
//            } else {
//                $member['agent'] = 0;
//            }
//
//            $myform = json_decode($member['yz_member']['member_form']);
//        }
//
//        $set = \Setting::get('shop.member');
//
//        if (empty($set['level_name'])) {
//            $set['level_name'] = '普通会员';
//        }
//
//        if (0 == $member['yz_member']['parent_id']) {
//            $parent_name = '总店';
//        } else {
//            $parent = Member::getMemberById($member['yz_member']['parent_id']);
//
//            $parent_name = $parent->nickname;
//        }
//
//        return view('member.detail', [
//            'member' => $member,
//            'levels' => $levels,
//            'groups' => $groups,
//            'set'    => $set,
//            'myform' => $myform,
//            'parent_name' => $parent_name
//        ])->render();
        // todo:优化 vue
        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;

        $type = request()->type?1: 0;    //用来判断返回模板还是json数据
        if ($uid == 0 || !is_int($uid)) {
            $this->message('参数错误', '', 'error');
            exit;
        }
        $member = Member::getMemberDetailBlackById($uid);
        $groups = MemberGroup::getMemberGroupList();
        $levels = MemberLevel::uniacid()
            ->select('id', 'level', 'level_name')
            ->get();
        $myform = [];
        if (!empty($member)) {
            $member = $member->toArray();

            if ($member['yz_member']['is_agent'] == 1 && $member['yz_member']['status'] == 2) {
                $member['agent'] = 1;
            } else {
                $member['agent'] = 0;
            }
            $member['createtime'] = date('Y-m-d H:i:s',$member['createtime']);
            $myform = json_decode($member['yz_member']['member_form'],true);
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
        if (!$member['yz_member']['validity']){
            $member['yz_member']['validity'] = 0;
        }
        if($type){
            return $this->successJson('ok',[
                'member' => $member,
                'levels' => $levels,
                'groups' => $groups,
                'set'    => $set,
                'myform' => $myform ,
                'parent_name' => $parent_name
            ]);
        }

        return view('member.detail1',[
            'member' => $member,
            'levels' => $levels,
            'groups' => $groups,
            'set'    => $set,
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
        if($invite_code == ''){
            $invite_code = '';
        }else{
            if(strlen($invite_code) != 8){
                return $this->errorJson('会员邀请码8个字符');
            }
            /*  if(preg_match("/^[a-zA-Z\s]+$/",$invite_code) || preg_match("/^[0-9\s]+$/",$invite_code)){
                  return $this->message('会员邀请码必须大写字母拼加数字组成8个字符', '', 'error');
              }*/
            if(preg_match("/^[0-9a-zA-Z\s]+$/",$invite_code)){
                $invite_code = strtoupper($invite_code);
            }
            $invite = MemberShopInfo::select('invite_code')
                ->where('invite_code',$invite_code)
                ->where('member_id','!=',$uid)
                ->count();
            if($invite){
                return $this->errorJson('会员邀请码已存在或参数错误');
            }
        }


        $member_model = Member::find($uid);
        $member_model->widgets = $parame['widgets'];

        $member_model->realname = $parame['realname'];
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
            $yz['status'] =  0;
            $yz['agent_time'] = 0;
        }

        //判断会员等级是否改变
        $is_upgrade = false;

        $new_level = MemberLevel::find($yz['level_id'])->level;
        if ($shopInfoModel->level_id != $yz['level_id'] && $new_level > $shopInfoModel->level->level) {
            $is_upgrade = true;
        }

        $shopInfoModel->fill($yz);
        $validator = $shopInfoModel->validator();
        if ($validator->fails()) {
            $this->error($validator->messages());
        } else {
            if ($shopInfoModel->save()) {
                if ($is_upgrade) {
                    //会员等级升级触发事件
                    event(new MemberLevelUpgradeEvent($shopInfoModel, true));
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
            (new \app\common\services\operation\MemberBankCardLog(['uid'=> $uid, 'is_black' => \YunShop::request()->black], 'special'));
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
     * 推广下线
     *
     * @return mixed
     */
    public function agentOld()
    {
        $request = \YunShop::request();

        $member_info = Member::getUserInfos($request->id)->first();

        if (empty($member_info)) {
            return $this->message('会员不存在','', 'error');
        }

        $list = Member::getAgentInfoByMemberId($request)
            ->paginate($this->pageSize)
            ->toArray();

        $pager = PaginationHelper::show($list['total'], $list['current_page'], $this->pageSize);

        return view('member.agent-old', [
            'member' => $member_info,
            'list'  => $list,
            'pager' => $pager,
            'total' => $list['total'],
            'request' => $request
        ])->render();
    }

    /**
     * 推广下线
     *
     * @return mixed
     */
    public function agent()
    {
        $request = \YunShop::request();

        $member_info = Member::getUserInfos($request->id)->first();

        if (empty($member_info)) {
            return $this->message('会员不存在','', 'error');
        }

        $list = MemberParent::children($request)
            ->orderBy('level','asc')
            ->orderBy('id','asc')
            ->paginate($this->pageSize)
            ->toArray();

        $level_total = MemberParent::where('parent_id', $request->id)
            ->selectRaw('count(member_id) as total, level, max(parent_id) as parent_id')
            ->groupBy('level')
            ->get();

        $pager = PaginationHelper::show($list['total'], $list['current_page'], $this->pageSize);

        return view('member.agent', [
            'member' => $member_info,
            'list'  => $list,
            'pager' => $pager,
            'total' => $list['total'],
            'request' => $request,
            'level_total' => $level_total,
        ])->render();
    }

    public function agentExport()
    {
        $file_name = date('Ymdhis', time()) . '会员下级导出';
        $export_data[0] = ['ID', '昵称', '真实姓名', '电话'];
        $member_id = request()->id;
        $child = MemberParent::where('parent_id', $member_id)->with('hasOneChildMember')->get();
        foreach ($child as $key => $item) {
            $member = $item->hasOneChildMember;
            $export_data[$key + 1] = [
                $member->uid,
                changeSpecialSymbols($member->nickname),
                $member->realname,
                $member->mobile,
            ];
        }
        \Excel::create($file_name, function ($excel) use ($export_data) {
            // Set the title
            $excel->setTitle('Office 2005 XLSX Document');

            // Chain the setters
            $excel->setCreator('芸众商城')
                ->setLastModifiedBy("芸众商城")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("report file");

            $excel->sheet('info', function ($sheet) use ($export_data) {
                $sheet->rows($export_data);
            });
        })->export('xls');
    }




    /**
     * 推广上线
     * @throws \Throwable
     */
    public function agentParent()
    {
        $request = \YunShop::request();

        $member_info = Member::getUserInfos($request->id)->first();

        if (empty($member_info)) {
            return $this->message('会员不存在','', 'error');
        }

        $list = MemberParent::parent($request)->orderBy('level','asc')->paginate($this->pageSize)->toArray();

        $pager = PaginationHelper::show($list['total'], $list['current_page'], $this->pageSize);

        return view('member.agent-parent', [
            'member' => $member_info,
            'list'  => $list,
            'pager' => $pager,
            'total' => $list['total'],
            'request' => $request
        ])->render();
    }

    /**
     * 推广上线导出
     */
    public function agentParentExport()
    {
        $file_name = date('Ymdhis', time()) . '会员上级导出';
        $export_data[0] = ['ID', '昵称', '真实姓名', '电话'];
        $member_id = request()->id;

        $child = MemberParent::where('member_id', $member_id)->with(['hasOneMember'])->get();
        foreach ($child as $key => $item) {
            $member = $item->hasOneMember;
            $export_data[$key + 1] = [
                $member->uid,
                changeSpecialSymbols($member->nickname),
                $member->realname,
                $member->mobile,
            ];
        }

        \Excel::create($file_name, function ($excel) use ($export_data) {
            // Set the title
            $excel->setTitle('Office 2005 XLSX Document');

            // Chain the setters
            $excel->setCreator('芸众商城')
                ->setLastModifiedBy("芸众商城")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("report file");

            $excel->sheet('info', function ($sheet) use ($export_data) {
                $sheet->rows($export_data);
            });
        })->export('xls');
    }

    public function firstAgentExport()
    {
        $export_data = [];
        $file_name = date('Ymdhis', time()) . '会员直推上级导出';

        $member_id = request()->id;
        $team_list = TeamDividendLevelModel::getList()->get();

        foreach ($team_list as $level) {
            $export_data[0][] = $level->level_name;
            $levelId[] = $level->id;
        }
        array_push($export_data[0], '会员ID', '会员', '姓名/手机号码');

        $child = MemberParent::where('member_id', $member_id)
            ->where('level', 1)
            ->with(['hasManyParent' => function($q) {
                $q->orderBy('level','asc');
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

        \Excel::create($file_name, function ($excel) use ($export_data) {
            // Set the title
            $excel->setTitle('Office 2005 XLSX Document');

            // Chain the setters
            $excel->setCreator('芸众商城')
                ->setLastModifiedBy("芸众商城")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("report file");

            $excel->sheet('info', function ($sheet) use ($export_data) {
                $sheet->rows($export_data);
            });
        })->export('xls');
    }

    public function getLevel($member, $levelId)
    {
        $data = [];
//        $num = count($member->hasManyParentTeam);
        foreach ($levelId as $k => $value) {
            foreach ($member->hasManyParent as $key => $parent) {
                if ($parent->hasOneTeamDividend->hasOneLevel->id == $value) {
                    $data[$k] = $parent->hasOneMember->nickname.' '.$parent->hasOneMember->realname.' '.$parent->hasOneMember->mobile;
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
        $member_builder = Member::searchMembers(\YunShop::request());
        $export_page = request()->export_page ? request()->export_page : 1;
        $export_model = new ExportService($member_builder, $export_page);

        $file_name = date('Ymdhis', time()) . '会员导出'.$export_page;

        $export_data[0] = ['会员ID', '推荐人','推荐人ID','推荐人手机号','粉丝', '姓名', '手机号', '等级', '分组', '注册时间', '积分', '余额', '订单', '金额', '关注', '提现手机号'];

        foreach ($export_model->builder_model->toArray() as $key => $item) {
            if (!empty($item['yz_member']) && !empty($item['yz_member']['agent'])) {
                $agent = $item['yz_member']['agent']['nickname'];

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

            $order = $item['has_one_order']['total']?:0;
            $price = $item['has_one_order']['sum']?:0;

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
                $item['nickname'] = '，' . $item['nickname'];
            }

            if (!$item['yz_member']['parent_id']){
                $parent_id = 0;
            }else{
                $parent_id = $item['yz_member']['parent_id'];
            }

            if (!$item['yz_member']['agent'] || !$item['yz_member']['agent']['mobile']){
                $parent_mobile = '';
            }else{
                $parent_mobile = $item['yz_member']['agent']['mobile'];
            }

            $export_data[$key + 1] = [$item['uid'],$agent,$parent_id,$parent_mobile, $item['nickname'], $item['realname'], $item['mobile'],
                $level, $group, date('Y-m-d H:i:s', $item['createtime']), $item['credit1'], $item['credit2'], $order,
                $price, $fans, $item['yz_member']['withdraw_mobile']];
        }

        $export_model->export($file_name, $export_data, \Request::query('route'));
    }


    public function search_member()
    {
        $members    = [];
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

        return view('member.query', [
            'members' => $members
        ])->render();
    }

    //查找用户
    //Todo 后台优化（原方法search_member,其他方法可能用到，暂未删除）
    public function searchMember()
    {
        $members    = [];
        $parent_id = request()->parent;

        if (is_numeric($parent_id)) {
            $member = Member::getMemberById($parent_id);

            if (!is_null($member)) {
                $members[] = $member->toArray();
            }

            if ($parent_id == 0) {
                //返回总店
                $members[] = ['uid'=>0,'nickname'=>'总店'];
            }
        }

        return $this->successJson('ok',[
            'members' => $members
        ]);
    }

    //修改会员上线
    public function change_relation()
    {
//        $parent_id = \YunShop::request()->parent;
//        $uid       = \YunShop::request()->member;
//
//        $msg = MemberShopInfo::change_relation($uid, $parent_id);
//
//        switch ($msg['status']) {
//            case -1:
//                return $this->message('上线没有推广权限', yzWebUrl('member.member.detail'), 'warning');
//                break;
//            case 0:
//                response(['status' => 0])->send();
//                break;
//            case 1:
//                response(['status' => 1])->send();
//                break;
//            default:
//                response(['status' => 0])->send();
//        }

        $parent_id = request()->parent;
        $uid       = request()->member;

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

    //会员上线修改记录
    public function member_record()
    {
        $records = MemberRecord::getRecord(request()->uid);

        return $this->successJson('ok',[
            'records' => $records
        ]);
//
//        return view('member.record', [
//            'records' => $records
//        ])->render();
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

            $total       = $member_info->count();
            $total_page  = ceil($total/$pageSize);

            \Log::debug('------total-----', $total);
            \Log::debug('------total_page-----', $total_page);

            Cache::put('queque_wechat_total', $total_page, 30);

            for ($curr_page = 1; $curr_page <= $total_page; $curr_page++) {
                \Log::debug('------curr_page-----', $curr_page);
                $offset      = ($curr_page - 1) * $pageSize;
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
        $page  = Cache::get('queque_wechat_page') ?: 0;
        \Log::debug('--------ajax total-------', $total);
        \Log::debug('--------ajax page-------', $page);
        if ($total == $page) {
            return json_encode(['status' => 1]);
        } else {
            return json_encode(['status' => 0]);
        }

        /*ry {
            \Artisan::call('syn:wechatUnionid' ,['uniacid'=>$uniacid]);


            return json_encode(['status' => 1]);
        } catch (\Exception $e) {
            return json_encode(['status' => 0]);
        }*/
    }

    public function exportRelation()
    {
        $uniacid = \YunShop::app()->uniacid;
        $parentMemberModle = new ParentOfMember();
        $childMemberModel = new ChildrenOfMember();
        $parentMemberModle->DeletedData($uniacid);
        $childMemberModel->DeletedData($uniacid);

        $member_relation = new MemberRelation();

        $member_relation->createParentOfMember();

        return view('member.export-relation', [])->render();
    }

    public function exportRelation2()
    {
        $uniacid = \YunShop::app()->uniacid;

        $job = (new \app\Jobs\MemberMaxRelatoinJob($uniacid));
        dispatch($job);

        return view('member.export-relation', [])->render();
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

        //合并记录
        $data = [
            'uniacid' => \YunShop::app()->uniacid,
            'member_id' => $yz_member->member_id,
            'mark_member_id' => $yz_member->mark_member_id,
            'created_at' => time(),
        ];

        $exception = DB::transaction(function () use ($yz_member, $data) {
            //小程序
            $memberMini = MemberMiniAppModel::getFansById($yz_member->mark_member_id);
            if (!empty($memberMini)) {
                MemberMiniAppModel::where('member_id', $yz_member->mark_member_id)->update(['member_id' => $yz_member->member_id]);
            }

            //公众号
            $memberFans = McMappingFans::getFansById($yz_member->mark_member_id);
            if (!empty($memberFans)) {
                McMappingFans::where('uid', $yz_member->mark_member_id)->update(['uid' => $yz_member->member_id]);
            }

            //app
            $memberWechat = \app\frontend\modules\member\models\MemberWechatModel::getFansById($yz_member->mark_member_id);
            if (!empty($memberWechat)) {
                \app\frontend\modules\member\models\MemberWechatModel::where('member_id', $yz_member->mark_member_id)->update(['member_id' => $yz_member->member_id]);
            }

            //统一
            $memberUni_main = MemberUnique::where('member_id', $yz_member->member_id)->first();
            $memberUni = MemberUnique::where('member_id', $yz_member->mark_member_id)->first();
            if (empty($memberUni_main) && !empty($memberUni)) {
                MemberUnique::where('member_id', $yz_member->mark_member_id)->update(['member_id' => $yz_member->member_id]);
            }

            Member::where('uid', $yz_member->mark_member_id)->update(['mobile' => '', 'credit1' => 0, 'credit2' => 0]);  //mc_members清空数据
            MemberShopInfo::where('member_id', $yz_member->mark_member_id)->delete();  //软删除yz_member

            MemberMergeLog::insert($data); //添加合并记录

            $yz_member->is_old = 0;
            $yz_member->mark_member_id = 0;

            $yz_member->save();
        });

        if (is_null($exception)) {
            return $this->successJson('合并成功');
        } else {
            return $this->errorJson('合并失败');
        }
    }

    public function memberChart()
    {
        $type = request()->chart_type;

        $count = 0;

        switch ($type) {
            case 1 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->where('mc_members.mobile', '!=', '')
                    ->count();
                break;
            case 2 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->join('mc_mapping_fans', 'mc_members.uid', '=', 'mc_mapping_fans.uid')
                    ->where('mc_mapping_fans.uniacid', \YunShop::app()->uniacid)
                    ->count();
                break;
            case 3 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->join('yz_member_mini_app', 'mc_members.uid', '=', 'yz_member_mini_app.member_id')
                    ->where('yz_member_mini_app.uniacid', \YunShop::app()->uniacid)
                    ->count();
                break;
            case 4 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->join('yz_member_wechat', 'mc_members.uid', '=', 'yz_member_wechat.member_id')
                    ->where('yz_member_wechat.uniacid', \YunShop::app()->uniacid)
                    ->count();
                break;
            case 5 :
                $count = Member::uniacid()->select(['uid', 'mobile'])
                    ->join('yz_member', 'mc_members.uid', '=', 'yz_member.member_id')
                    ->whereNull('yz_member.deleted_at')
                    ->join('yz_member_unique', 'mc_members.uid', '=', 'yz_member_unique.member_id')
                    ->where('yz_member_unique.uniacid', \YunShop::app()->uniacid)
                    ->count();
                break;
        }

        return $this->successJson('ok', [
            'count' => $count,
        ]);
    }
}
