<?php

namespace app\frontend\controllers;

use app\backend\modules\member\models\MemberRelation;
use app\common\components\BaseController;
use app\common\facades\RichText;
use app\common\facades\Setting;
use app\common\models\Protocol;
use app\framework\Http\Request;
use app\frontend\models\Member;
use app\frontend\models\MemberShopInfo;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/25
 * Time: 上午11:57
 */
class SettingController extends BaseController
{
    protected $_lang;

    public function preAction()
    {
        $this->_lang = 'zh_cn';
        parent::preAction();

    }

    /**
     * 商城设置接口
     * @param string $key setting表key字段值
     * @return
     */
    public function get()
    {
        $key = \YunShop::request()->setting_key ? \YunShop::request()->setting_key : 'shop';
        if (!empty($key)) {
            $setting = Setting::get('shop.' . $key);
        } else {
            $setting = Setting::get('shop');
        }

        if (!$setting) {
            return $this->errorJson('未进行设置.');
        }

        $setting['logo'] = replace_yunshop(tomedia($setting['logo']));
/*
        $relation = MemberRelation::getSetInfo()->first();

        if ($relation) {
            $setting['agent'] = $relation->status ? true : false;
        } else {
            $setting['agent'] = false;
        }
*/
        $setting['agent'] = true;
        
        //强制绑定手机号
        $member_set = Setting::get('shop.member');

        if ((1 == $member_set['is_bind_mobile']) && \YunShop::app()->getMemberId() && \YunShop::app()->getMemberId() > 0) {
            $member_model = Member::getMemberById(\YunShop::app()->getMemberId());

            if ($member_model && $member_model->mobile) {
                $setting['is_bind_mobile'] = 0;
            } else {
                $setting['is_bind_mobile'] = 1;
            }
        } else {
            $setting['is_bind_mobile'] = 0;
        }

        return $this->successJson('获取商城设置成功', $setting);

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 会员注册协议
     */
    public function getMemberProtocol(Request $request,$integrated = null)
    {
        $register = Setting::get('shop.register');
        if (!empty($register['top_img'])) {
            $register['top_img'] = yz_tomedia($register['top_img']);
        }
        $member = Setting::get('shop.member');
        $custom_field = [
            'is_custom'    => $member['is_custom'],
            'custom_title' => $member['custom_title'],
            'is_validity'  => $member['level_type'] == 2 ? true : false,
            'term'         => $member['term'] ? $member['term'] : 0,
        ];
        $form = Setting::get('shop.form');
        $data = Protocol::uniacid()
            ->select('status as protocol', 'content', 'title', 'default_tick')
            ->first();
        $data['content'] = html_entity_decode($data['content']);
        if (!$register) {
            $register = [
                'top_img' => '',
                'top_img_url' => '',
                'is_password' => 1,
                'title1' => '',
                'title2' => '',
                'protocol' => [
                    'title' => '',
                    'status' => 1,
                ],
            ];
        }
        $data['register'] = $register;
        $data['form'] = json_decode($form, true);
        $data['custom_field'] = $custom_field;
        $data['protocol'] = $data->protocol !== 0 ? 1 : $data->protocol;
        $data['default_tick'] = $data->default_tick !== 0 ? 1 : $data->default_tick;
        $member = MemberShopInfo::uniacid()
            ->select(['member_id', 'province_name', 'city_name', 'area_name', 'address', 'custom_value', 'member_form'])
            ->where('member_id', \YunShop::app()->getMemberId())
            ->first();
        if ($member) {
            $member = $member->toArray();
        }
        if (!empty($member) && !is_null($member['member_form'])) {
            $member['member_form'] = json_decode($member['member_form']);
        }
        $mc_member = Member::uniacid()
            ->select(['uid', 'gender', 'birthyear', 'birthmonth', 'birthday'])
            ->where('uid', \YunShop::app()->getMemberId())
            ->first();
        if ($mc_member) {
            $mc_member = $mc_member->toArray();
        }
        $member_result = array_merge($member, $mc_member);
        $data['member'] = $member_result;
        if(empty($data)){
            $data = ['protocol' => 1, 'content' => '', 'title' => '', 'register' => [], 'form' => [], 'custom_field' => []];
        }
	    $shop = Setting::get('shop.shop');
		if ($shop['is_agreement']){
			$data['new_agreement'] = RichText::get('shop.agreement');
			$data['agreement_name'] = $shop['agreement_name'];
		}
        if(is_null($integrated)){
            return $this->successJson('获取注册协议成功', $data);
        }else{
            return show_json(1,$data);
        }
    }

    //获取余额设置
    public function getBalance()
    {
        $shop = Setting::get('shop.shop');
        $credit=$shop['credit'] ?: '余额';
        return $this->successJson('获取余额设置成功',['balance'=>$credit]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 注册自定义表单接口
     */
    public function getRegisterDiyForm(Request $request, $integrated = null)
    {
        $member_set = Setting::get('shop.member');

        $is_diyform = \YunShop::plugin()->get('diyform');
        $data = [
            'form_id' => 0,
            'status' => 0,
        ];
        if ($is_diyform) {
            $data['form_id'] = $member_set['form_id'];
            $data['status'] = $data['form_id'] ? 1 : 0;
        }
        if(is_null($integrated)){
            return $this->successJson('返回数据成功',$data);
        }else{
            return show_json(1,$data);
        }
    }

    public function getLangSetting()
    {
        $lang = Setting::get('shop.lang.lang');

        $data = [
            'test' => [],
            'commission' => [
                'title' => '',
                'commission' => '',
                'agent' => '',
                'level_name' => '',
                'commission_order' => '',
                'commission_amount' => '',
            ],
            'single_return' => [
                'title' => '',
                'single_return' => '',
                'return_name' => '',
                'return_queue' => '',
                'return_log' => '',
                'return_detail' => '',
                'return_amount' => '',
            ],
            'team_return' => [
                'title' => '',
                'team_return' => '',
                'return_name' => '',
                'team_level' => '',
                'return_log' => '',
                'return_detail' => '',
                'return_amount' => '',
                'return_rate' => '',
                'team_name' => '',
                'return_time' => '',
            ],
            'full_return' => [
                'title' => '',
                'full_return' => '',
                'return_name' => '',
                'full_return_log' => '',
                'return_detail' => '',
                'return_amount' => '',
            ],
            'team_dividend' => [
                'title' => '',
                'team_dividend' => '',
                'team_agent_centre' => '',
                'dividend' => '',
                'flat_prize' => '',
                'award_gratitude' => '',
                'dividend_amount' => '',
            ],
            'area_dividend' => [
                'area_dividend_center' => '',
                'area_dividend' => '',
                'dividend_amount' => '',
            ]
        ];

        $langData = Setting::get('shop.lang.' . $lang, $data);

        if (is_null($langData)) {
            $langData = $data;
        }

        return $this->successJson('获取商城语言设置成功', $langData);
    }

}