<?php

namespace app\backend\modules\member\controllers;

use app\backend\modules\member\models\MemberRelation as Relation;
use app\common\components\BaseController;
use app\common\facades\RichText;
use app\common\facades\Setting;
use app\common\helpers\Cache;
use app\common\models\Goods;
use app\common\models\notice\MessageTemp;
use app\common\models\Protocol;
use iscms\AlismsSdk\AlibabaAliqinFcSmsNumSendRequest;
use Yunshop\Diyform\models\DiyformTypeModel;

class MemberSetController extends BaseController
{
    public function basic()
    {
        //todo 这里需判断权限，自动跳转
        $relationSet = Setting::get('shop.relation_base');
        $memberSet = Setting::get('shop.member');
        $temp_list = MessageTemp::getList();
        $member_agent_default = MessageTemp::getTempIdByNoticeType('member_agent');
        $member_new_lower_default = MessageTemp::getTempIdByNoticeType('member_new_lower');
        return view('member.memberSet.basic', [
            'set' => [
                'banner' => $relationSet['banner'] ? : '',
                'banner_url' => $relationSet['banner'] ? yz_tomedia($relationSet['banner']) : '',
                'headimg' => $memberSet['headimg'] ? : '',
                'headimg_url' => $memberSet['headimg'] ? yz_tomedia($memberSet['headimg']) : '',
                'member_agent'     => $relationSet['member_agent'] ? : "",
                'member_new_lower' => $relationSet['member_new_lower'] ? : "",
                'member_agent_default' => ($relationSet['member_agent'] && $relationSet['member_agent'] == $member_agent_default->id) ? "1" : "0",
                'member_new_lower_default' => ($relationSet['member_new_lower'] && $relationSet['member_new_lower'] == $member_new_lower_default->id) ? "1" : "0",
            ],
            'temp_list' => $temp_list,
        ])->render();
    }

    public function basicStore()
    {
        $set = request()->set;
        $relationSet = Setting::get('shop.relation_base');
        $relationSet['banner'] = $set['banner'] ? : '';
        $relationSet['member_agent'] = $set['member_agent'] ? : '';
        $relationSet['member_new_lower'] = $set['member_new_lower'] ? : '';
        Setting::set('shop.relation_base',$relationSet);
        $memberSet = Setting::get('shop.member');
        $memberSet['headimg'] = $set['headimg'] ? : '';
        if (!$this->shopMemberSave($memberSet)) {
            $this->errorJson('会员设置失败');
        }
        return $this->successJson('保存成功');
    }

    public function registerAndLogin()
    {
        $memberSet = Setting::get('shop.member');
        $registerSet = Setting::get('shop.register');
        $relationSet = Setting::get('shop.relation_base');

        return view('member.memberSet.registerLogin', [
            'set' => [
                'get_register' => $memberSet['get_register'] ? "1" : "0",
                'is_password'  => $registerSet['is_password'] ? "1" : "0",
                'login_mode'   => $registerSet['login_mode'] ?  : ['mobile_code','password'],
                'wechat_login_mode' => $memberSet['wechat_login_mode'] ? "1" : "0",
                'is_bind_mobile' => $memberSet['is_bind_mobile'] ?  : "0",
                'bind_mobile_page' => $memberSet['bind_mobile_page'] ? : [],
                'is_bind_address' => $memberSet['is_bind_address'] ?  : "0",
                'bind_address_page' => $memberSet['bind_address_page'] ? : [],
                'top_img' => $registerSet['top_img'] ? : '',
                'top_img_url' => $registerSet['top_img'] ? yz_tomedia($registerSet['top_img']) : '',
                'title1'   => $registerSet['title1'] ?  : '',
                'title2'   => $registerSet['title2'] ?  : '',
                'is_member_merge' => $relationSet['is_member_merge'] ? : 0,
                'is_merge_save_level' => $relationSet['is_merge_save_level'] ? : 0,
                'is_invite' => $memberSet['is_invite'] ? "1" : "0",
                'required' => $memberSet['required'] ? "1" : "0",
                'default_invite' => $memberSet['default_invite'] ? : "",
                'invite_page' => $memberSet['invite_page'] ? "1" : "0",
                'is_bind_invite' => $memberSet['is_bind_invite'] ? "1" : "0",
                'login_page_mode' => $registerSet['login_page_mode'] ? : 0,
                'login_banner'   => $registerSet['login_banner'] ?  : '',
                'login_banner_url'   => $registerSet['login_banner'] ?  yz_tomedia($registerSet['login_banner']): '',
                'login_diy_url'   => $registerSet['login_diy_url'] ?  : '',
             ],
        ])->render();
    }

    public function registerAndLoginStore()
    {
        $set = request()->set;

        $memberSet = Setting::get('shop.member');
        $memberSet['get_register'] = $set['get_register'] ? "1" : "0";
        $memberSet['wechat_login_mode'] = $set['wechat_login_mode'] ? "1" : "0";
        $memberSet['is_bind_mobile'] = $set['is_bind_mobile'] ? : "0";
        $memberSet['bind_mobile_page'] = $set['bind_mobile_page'] ? : [];
        $memberSet['is_bind_address'] = $set['is_bind_address'] ? : "0";
        $memberSet['bind_address_page'] = $set['bind_address_page'] ? : [];
        $memberSet['is_invite'] = $set['is_invite'] ? "1" : "0";
        $memberSet['required'] = $set['required'] ? "1" : "0";
        $memberSet['default_invite'] = $set['default_invite'] ? : "";
        $memberSet['invite_page'] = $set['invite_page'] ? "1" : "0";
        $memberSet['is_bind_invite'] = $set['is_bind_invite'] ? "1" : "0";


        $registerSet = Setting::get('shop.register');
        $registerSet['is_password'] = $set['is_password'] ? "1" : "0";
        $registerSet['login_mode'] = $set['login_mode'] ? : [];
        if (!$registerSet['login_mode']) {
            return $this->errorJson('必须选择一种登录方式');
        }
        $registerSet['top_img'] = $set['top_img'] ? : "";
        $registerSet['title1'] = $set['title1'] ? : "";
        $registerSet['title2'] = $set['title2'] ? : "";
        $registerSet['login_page_mode'] = $set['login_page_mode'] ? : 0;
        $registerSet['login_banner'] = $set['login_banner'] ? : "";
        $registerSet['login_diy_url'] = $set['login_diy_url'] ? : "";

        $relationSet = Setting::get('shop.relation_base');
        $relationSet['is_member_merge'] = $set['is_member_merge'] ? : 0;
        $relationSet['is_merge_save_level'] = $set['is_merge_save_level'] ? : 0;
        if ($memberSet['is_bind_mobile'] !== '0' && $memberSet['invite_page'] == '1') {
            return $this->errorJson('强制绑定手机号跟邀请页面不能同时开启');
        }
        if (!$this->shopMemberSave($memberSet)) {
            $this->errorJson('会员设置失败');
        }

        Setting::set('shop.register',$registerSet);
        if (Cache::has('shop_register')) {
            Cache::forget('shop_register');
        }
        Setting::set('shop.relation_base',$relationSet);
        return $this->successJson('保存成功');
    }

    public function information()
    {
        $formSet = json_decode(Setting::get('shop.form'),true);
        $form = array_values(array_sort($formSet['form'], function ($value) {
            return $value['sort'];
        }));

        $memberSet = Setting::get('shop.member');
        $diyForm = [];
        if (app('plugins')->isEnabled('diyform')) {
            $diyForm = DiyformTypeModel::getDiyformList()->get();

        }

        return view('member.memberSet.information', [
            'set' => [
                'basic_register' => $formSet['base']['basic_register'] ? : "0",
                'name' => (bool)$formSet['base']['name'],
                'name_must' => $formSet['base']['name_must'] ? : "0",
                'sex' => (bool)$formSet['base']['sex'],
                'sex_must' => $formSet['base']['sex_must'] ? : "0",
                'address' => (bool)$formSet['base']['address'],
                'address_must' => $formSet['base']['address_must'] ? : "0",
                'birthday' => (bool)$formSet['base']['birthday'],
                'birthday_must' => $formSet['base']['birthday_must'] ? : "0",
                'change_info' => $formSet['base']['change_info'] ? : "0",
                'is_custom' => $memberSet['is_custom'] ? : "0",
                'is_custom_register' => $memberSet['is_custom_register'] ? : "0",
                'custom_title' => $memberSet['custom_title'] ? : "",
                'form_register' => $formSet['base']['form_register'] ? : "0",
                'form_open' => $formSet['base']['form_open'] ? : "0",
                'form_edit' => $formSet['base']['form_edit'] ? : "0",
                'form' => $form ? : [],
                'form_id' => $memberSet['form_id'] ? : "",
                'form_id_register' => $memberSet['form_id_register'] ? : "0",
                'idcard' => (bool)$formSet['base']['idcard'],
                'idcard_must' => $formSet['base']['idcard_must'] ? : "0",
                'idcard_addr' => (bool)$formSet['base']['idcard_addr'],
                'idcard_addr_must' => $formSet['base']['idcard_addr_must'] ? : "0",
            ],
            'diyForm' => $diyForm
        ])->render();
    }

    public function informationStore()
    {
        $set = request()->set;
        $formSet = json_decode(Setting::get('shop.form'),true);
        $memberSet = Setting::get('shop.member');
        $memberSet['is_custom'] = $set['is_custom'] ? : "0";
        $memberSet['is_custom_register'] = $set['is_custom_register'] ? : "0";
        $memberSet['custom_title'] = $set['custom_title'] ? : "";
        $memberSet['form_id'] = $set['form_id'] ? : "";
        $memberSet['form_id_register'] = $set['form_id_register'] ? : "0";
        $base = $formSet['base'];
        $base['basic_register'] = $set['basic_register'] ? : "0";
        $base['name'] = (bool)$set['name'];
        $base['name_must'] = $set['name_must'] ? : "0";
        $base['sex'] = (bool)$set['sex'];
        $base['sex_must'] = $set['sex_must'] ? : "0";
        $base['address'] = (bool)$set['address'];
        $base['address_must'] = $set['address_must'] ? : "0";
        $base['birthday'] = (bool)$set['birthday'];
        $base['birthday_must'] = $set['birthday_must'] ? : "0";
        $base['change_info'] = $set['change_info'] ? : "0";
        $base['form_register'] = $set['form_register'] ? : "0";
        $base['form_open'] = $set['form_open'] ? : "0";
        $base['form_edit'] = $set['form_edit'] ? : "0";
        $base['idcard'] = (bool)$set['idcard'];
        $base['idcard_must'] = $set['idcard_must'] ? : "0";
        $base['idcard_addr'] = (bool)$set['idcard_addr'];
        $base['idcard_addr_must'] = $set['idcard_addr_must'] ? : "0";
        $form = [];
        foreach ($set['form'] as $value) {
            if (empty($value['name'])) {
                return $this->successJson('自定义表单数据错误');
            }

            $sort = $value['sort']?:99;
            $pinyin = implode('', pinyin($value['name']));
            $form[] =['name'=>$value['name'], 'sort'=>$sort, 'del'=>0, 'pinyin'=>$pinyin, 'value'=>''];
        }
        $form = array_values(array_sort($form, function ($value) {
            return $value['sort'];
        }));
        Setting::set('shop.form', json_encode(['base'=>$base, 'form'=>$form]));
        if (!$this->shopMemberSave($memberSet)) {
            $this->errorJson('会员设置失败');
        }

        return $this->successJson('保存成功');
    }

    public function level()
    {
        $memberSet = Setting::get('shop.member');
        return view('member.memberSet.level', [
            'set' => [
                'level_name' => $memberSet['level_name'] ? : '普通会员',
                'display_page' => $memberSet['display_page'] ? "1" : "0",
                'level_type' => $memberSet['level_type'] ? : "0",
                'level_after' => $memberSet['level_after'] ? : "0",
                'term' => $memberSet['term'] ? "1" : "0",
                'level_discount_calculation' => $memberSet['level_discount_calculation'] ? "1" : "0",
                'discount' => $memberSet['discount'] ? : "1",
                'vip_price' => $memberSet['vip_price'] ? : "2",
            ],
        ])->render();
    }

    public function levelStore()
    {
        $set = request()->set;
        $memberSet = Setting::get('shop.member');
        $memberSet['level_name'] = $set['level_name'] ? : '';
        $memberSet['display_page'] = $set['display_page']? "1" : "0";
        $memberSet['level_type'] = $set['level_type'];
        $memberSet['level_after'] = $set['level_after'];
        $memberSet['term'] = $set['term'];
        $memberSet['level_discount_calculation'] = $set['level_discount_calculation'];
        $memberSet['discount'] = $set['discount'];
        $memberSet['vip_price'] = $set['vip_price'];

        if (!$this->shopMemberSave($memberSet)) {
            $this->errorJson('会员设置失败');
        }

        return $this->successJson('会员设置成功');
    }

    public function relation()
    {
        $relationSet = Setting::get('member.relation');
        $relation = Relation::uniacid()->first();
        if (!empty($relation)) {
            $relation = $relation->toArray();
        }
        if (!empty($relation['become_term'])) {
            $relation['become_term'] = array_values(unserialize($relation['become_term']));
        }
        $goods = [];
        if (!empty($relation['become_goods'])) {
            $relation_goods = unserialize($relation['become_goods']);
            $goods_ids = array_column($relation_goods,'goods_id');
            // 查询当前未被删除的商品
            $goods_ids && $current_goods = Goods::uniacid()->select('id', 'title', 'thumb')
                ->whereIn('id', $goods_ids)
                ->whereNull('deleted_at')
                ->get();
            if (!empty($current_goods)) {
                $current_goods = $current_goods->toArray();
                foreach ($current_goods as $key => $value) {
                    $current_goods[$key]['thumb'] = yz_tomedia($value['thumb']);
                }
                $goods = $current_goods;
            }
        }
        return view('member.memberSet.relation', [
            'set' => [
                'is_sales_commission' => app('plugins')->isEnabled('sales-commission') ? 1 : 0,
                'status' => $relation['status'] ? (string)$relation['status'] : '0',
                'become' => $relation['become'] ? : 0,
                'become_term' => $relation['become_term'] ? : [],
                'goods' => $goods,
                'become_order' => $relation['become_order'] ? : 0,
                'become_ordercount'   => $relation['become_ordercount'] ? : 0,
                'become_moneycount'   => $relation['become_moneycount'] ? : 0,
                'become_selfmoney'    => $relation['become_selfmoney'],
                'become_child' => $relation['become_child'] ? : 0,
                'become_check' => $relation['become_check'] ? : 0,
                'reward_points'  => $relation['reward_points'] ? : '',
                'maximum_number' => $relation['maximum_number'] ? : 0,
                'is_jump'          => $relationSet['is_jump'] ? : 0,
                'jump_link'        => $relationSet['jump_link'] ? : "",
                'small_jump_link'  => $relationSet['small_jump_link'] ? : "",
                'share_page'       => $relation['share_page'] ? (string)$relation['share_page'] : "0",
                'share_page_deail' => $relation['share_page_deail'] ? (string)$relation['share_page_deail'] : "0",
            ],
        ])->render();
    }

    public function relationStore()
    {
        $set = request()->set;
        $relationSet = Setting::get('member.relation');
        $relationSet['is_jump'] = $set['is_jump'] ? : 0;
        $relationSet['jump_link'] = $set['jump_link'] ? : "";
        $relationSet['small_jump_link'] = $set['small_jump_link'] ? : "";
        Setting::set('member.relation', $relationSet);

        $become_term = [];
        foreach ($set['become_term'] as $item) {
            $become_term[$item] = $item;
        }
        $relation = Relation::uniacid()->first();
        if (!$relation) {
            $relation = new Relation(['uniacid'=>\YunShop::app()->uniacid]);
        }
        $relation->status = $set['status'] ? : 0;
        $relation->become = $set['become'] ? : 0;
        $relation->become_term = ($set['become']>1&&$become_term) ? serialize($become_term) : "";
        $relation->become_order = $set['become_order'] ? : 0;
        $relation->become_ordercount = $set['become_ordercount'] ? : 0;
        $relation->become_moneycount = $set['become_moneycount'] ? : 0;
        $relation->become_selfmoney = $set['become_selfmoney'] ? : "";
        $relation->become_child = $set['become_child'] ? : 0;
        $relation->become_check = $set['become_check'] ? : 0;
        $relation->reward_points = $set['reward_points'] ? : '';
        $relation->maximum_number = $set['maximum_number'];
        $relation->share_page = $set['share_page'] ? : "0";
        $relation->share_page_deail = $set['share_page_deail'] ? : "0";

        $goods_ids = array_column($set['goods'],'id');
        $goods = [];
        if ($goods_ids) {
            $goods = Goods::uniacid()->selectRaw('id as goods_id,title,thumb')
                ->whereIn('id', $goods_ids)
                ->whereNull('deleted_at')
                ->get()->toArray();
            $goods_ids = array_column($goods,'goods_id');
        }
        $relation->become_goods = $goods ? serialize($goods) : 0;
        $relation->become_goods_id = $goods_ids ? implode(',',$goods_ids) : "";
        $relation->save();
        Cache::forget('member_relation');
        return $this->successJson('ok');
    }

    public function memberCenter()
    {
        $memberSet = Setting::get('shop.member');

        $relationSet = Setting::get('shop.relation_base');
        $relation_level = [];
        $relationSet['relation_level'][0]==1 && $relation_level[] = "1";
        $relationSet['relation_level'][1]==2 && $relation_level[] = "2";

        $nameInfo = [];
        $relationSet['relation_level']['phone']==1 && $nameInfo[] = 'phone';
        $relationSet['relation_level']['realname']==1 && $nameInfo[] = 'realname';
        $relationSet['relation_level']['wechat']==1 && $nameInfo[] = 'wechat';

        return view('member.memberSet.member-center', [
            'set' => [
                'show_balance' => $memberSet['show_balance'] ? : '0',
                'show_point' => $memberSet['show_point'] ? : '0',
                'show_member_id' => $memberSet['show_member_id'] ? : '0',
                'is_referrer'  => empty($relationSet['is_referrer']) ? '0' : $relationSet['is_referrer'],
                'parent_is_referrer'  => empty($relationSet['parent_is_referrer']) ? '0' : $relationSet['parent_is_referrer'],
                'is_recommend_wechat'  => empty($relationSet['is_recommend_wechat']) ? '0' : $relationSet['is_recommend_wechat'],
                'relation_level'       => $relation_level,
                'name1'                => $relationSet['relation_level']['name1'] ? : '',
                'name2'                => $relationSet['relation_level']['name2'] ? : '',
                'nameInfo'             => $nameInfo,
                'is_statistical_goods' => $relationSet['is_statistical_goods'] ? : "0",
                'statistical_goods'    => $relationSet['statistical_goods'] ? : []
            ],
        ])->render();
    }

    public function memberCenterStore()
    {
        $set = request()->set;
        $memberSet = Setting::get('shop.member');
        $memberSet['show_balance'] = $set['show_balance'] ? : '0';
        $memberSet['show_point'] = $set['show_point'] ? : '0';
        $memberSet['show_member_id'] = $set['show_member_id'] ? : '0';
        if (!$this->shopMemberSave($memberSet)) {
            $this->errorJson('会员中心显示设置失败');
        }

        $relationSet = Setting::get('shop.relation_base');
        $relationSet['is_referrer'] = $set['is_referrer'] ? : '0';
        $relationSet['parent_is_referrer'] = $set['parent_is_referrer'] ? : '0';
        $relationSet['is_recommend_wechat'] = $set['is_recommend_wechat'] ? : '0';
        $relationSet['relation_level'][0] = in_array("1",$set['relation_level']) ? 1 : 0;
        $relationSet['relation_level'][1] = in_array("2",$set['relation_level']) ? 2 : 0;
        $relationSet['relation_level']['name1'] = $set['name1'] ? : "";
        $relationSet['relation_level']['name2'] = $set['name2'] ? : "";
        $relationSet['relation_level']['phone'] = in_array('phone',$set['nameInfo']) ? 1 : 0;
        $relationSet['relation_level']['realname'] = in_array('realname',$set['nameInfo']) ? 1 : 0;
        $relationSet['relation_level']['wechat'] = in_array('wechat',$set['nameInfo']) ? 1 : 0;
        $relationSet['is_statistical_goods'] = $set['is_statistical_goods'] ? : '0';
        $relationSet['statistical_goods'] = $set['statistical_goods'] ? : [];

        if (!Setting::set('shop.relation_base', $relationSet)) {
            $this->errorJson('会员中心显示设置失败');
        }
        return $this->successJson('ok');
    }

    public function agreement()
    {
        $protocol = Protocol::uniacid()->first();
        $shopSet = Setting::get('shop.shop');
        $agreement = RichText::get('shop.agreement');
        return view('member.memberSet.agreement', [
            'set' => [
                'register_status' => $protocol->status ? : "0",
                'register_title'  => $protocol->title ? : "",
                'register_content'  => $protocol->content ? : "",
                'register_default_tick'  => $protocol->default_tick ? : "0",
                'register_agreement_url'  => yzAppFullUrl('registerAgreement/'),
                'register_agreement_mini_url'  => "/packageF/new_info_v2/agreement/agreement",
                'is_agreement' => $shopSet['is_agreement'] ? : "0",
                'agreement_name' => $shopSet['agreement_name'] ? : "",
                'agreement' => $agreement ? : "",
            ],
        ])->render();
    }

    public function agreementStore()
    {
        $set = request()->set;
        $protocol = Protocol::uniacid()->first();
        if (!$protocol) {
            $protocol = new Protocol();
            $protocol->uniacid = \YunShop::app()->uniacid;
        }
        $protocol->status = $set['register_status'] ? : 0;
        $protocol->title = $set['register_title'] ? : "";
        $protocol->content = $set['register_content'] ? : "";
        $protocol->default_tick = $set['register_default_tick'] ? : 0;
        if (!$protocol->save()) {
            return $this->errorJson('协议保存失败');
        }
        $shopSet = Setting::get('shop.shop');
        $shopSet['is_agreement'] = $set['is_agreement'] ? : "0";
        $shopSet['agreement_name'] = $set['agreement_name'] ? : "";
        if (!$this->shopSetSave($shopSet)) {
            return $this->errorJson('协议保存失败');
        }

        RichText::set('shop.agreement', ($set['agreement']?:""));
        return $this->successJson('ok');
    }

    private function shopSetSave($data)
    {
        if (Cache::has('shop_setting')) {
            Cache::forget('shop_setting');
        }
        if (!Setting::set('shop.shop', $data)) {
            return false;
        }
        return true;
    }

    private function shopMemberSave($data)
    {
        if (Cache::has('shop_member')) {
            Cache::forget('shop_member');
        }
        if (!Setting::set('shop.member', $data)) {
            return false;
        }
        return true;
    }
}