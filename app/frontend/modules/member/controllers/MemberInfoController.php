<?php

namespace app\frontend\modules\member\controllers;

use app\common\components\ApiController;
use app\common\facades\RichText;
use app\common\facades\Setting;
use app\common\helpers\Cache;
use app\common\models\Member;
use app\common\models\member\MemberCancelSet;
use app\common\models\MemberShopInfo;
use app\common\models\Protocol;
use app\frontend\models\MembershipInformationLog;
use app\frontend\modules\member\models\MemberBankCard;
use app\frontend\modules\member\models\MemberModel;


class MemberInfoController extends ApiController
{
    public $publicAction = ["platformAgreement","registerAgreement"];
    public $ignoreAction = ["platformAgreement","registerAgreement"];

    public function userInfoUpdatePage()
    {
        $member_id = \YunShop::app()->getMemberId();
        $member = MemberModel::getUserInfos_v2($member_id)->first();
        if (empty($member)) {
            $mid = Member::getMid();
            $this->jumpUrl(request()->type, $mid);
        }
        $member = $member->toArray();
        $bankCard = MemberBankCard::where('member_id', $member_id)->first();
        $protocol = Protocol::uniacid()->first();
        $shopSet = Setting::get('shop.shop');
        $cancelSet = MemberCancelSet::uniacid()->first();
        $paySet = Setting::getByGroup('pay_password');
        $return_data = [
            'uid' => $member['uid'],
            'nickname' => $member['nickname'],
            'avatar' => yz_tomedia($member['avatar']),
            'wechat' => $member['yz_member']['wechat'] ? : '',
            'mobile' => $member['mobile'] ? : '',
            'alipay' => $member['yz_member']['alipay'] ? : '',
            'alipayname' => $member['yz_member']['alipayname'] ? : '',
            'bank_card'  =>$bankCard->bank_card,
            'converge_pay' => (app('plugins')->isEnabled('converge_pay') && Setting::get('plugin.convergePay_set.converge_pay_status')) ? 1 : 0,
            'member_cancel' => $cancelSet['status'] ? 1 : 0,
            'pay_state' => $paySet['pay_state'] ? 1 : 0,
            'register_agreement' => [
                'status' => $protocol->status ? 1 : 0,
                'title'  => $protocol->title ? : "注册协议"
             ],
            'platform_agreement' => [
                'status' => $shopSet['is_agreement'] ? 1 : 0,
                'title'  => $shopSet['agreement_name'] ? : "平台协议"
            ],
        ];
        return $this->successJson('ok',$return_data);
    }

    /**
     * 基本信息
     * @return mixed
     */
    public function userInfo()
    {
        try {
            $member_id = \YunShop::app()->getMemberId();
            $member = MemberModel::getUserInfos_v2($member_id)->first();
            if (empty($member)) {
                $mid = Member::getMid();
                \Log::debug('----------------基本信息获取',[request()->all()]);
                $this->jumpUrl(request()->type, $mid);
            }
            $member = $member->toArray();
            $formSet = json_decode(Setting::get('shop.form'),true);
            $shop_setting = Setting::get('shop.shop');
            $return_data = [
                'name' => $shop_setting['name'] ? : '商城',
                'uid' => $member['uid'],
                'nickname' => $member['nickname'],
                'avatar' => $member['avatar'],
                'avatar_image' => $member['avatar_image'],
                'can_update_nickname' => $formSet['base']['change_info'] ? 1 : 0,
                'can_update_avatar' => $formSet['base']['change_info'] ? 1 : 0,
            ];
            $return_data['basic_info'] = $this->basicInfo($member);
            $return_data['fixed_diy_field'] = $this->fixedDiyField($member);
            $return_data['diy_field'] = $this->diyField($member);
            $return_data['diy_form'] = $this->diyForm($member);

            $memberSet = Setting::get('shop.member');
            $return_data['get_register_diy_form'] =  [
                'form_id' => $memberSet['form_id'] ? : 0,
                'status' => (app('plugins')->isEnabled('diyform') && $memberSet['form_id']) ? 1 : 0,
            ];

            return $this->successJson('ok',$return_data);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    private function basicInfo($member)
    {
        $formSet = json_decode(Setting::get('shop.form'),true);
        $returnData[] = [
            'name' => '姓名',
            'field' => 'name',
            'must' => $formSet['base']['name_must'] ? : 0,
            'value' => $member['realname'],
            'show' => (bool)$formSet['base']['name']
        ];
        $returnData[] = [
            'name' => '性别',
            'field' => 'gender',
            'must' => $formSet['base']['sex_must'] ? 1 : 0,
            'value' => $member['gender'],
            'show' => (bool)$formSet['base']['sex']
        ];
        $returnData[] = [
            'name' => '详细地址',
            'field' => 'address',
            'must' => $formSet['base']['address_must'] ? 1 : 0,
            'value' => [
                'province' => $member['yz_member']['province'] ? : '',
                'city' => $member['yz_member']['city'] ? : '',
                'area' => $member['yz_member']['area'] ? : '',
                'province_name' => $member['yz_member']['province_name'] ? : '',
                'city_name' => $member['yz_member']['city_name'] ? : '',
                'area_name' => $member['yz_member']['area_name'] ? : '',
                'address' => $member['yz_member']['address'] ? : '',
            ],
            'show' => (bool)$formSet['base']['address']
        ];
        $returnData[] = [
            'name' => '生日',
            'field' => 'birthday',
            'must' => $formSet['base']['birthday_must'] ? 1 : 0,
            'value' => [
                'birthyear' => $member['birthyear'] ? : '',
                'birthmonth' => $member['birthmonth'] ? : '',
                'birthday' => $member['birthday'] ? : '',
            ],
            'show' => (bool)$formSet['base']['birthday']
        ];
        $returnData[] = [
            'name' => '身份证',
            'field' => 'idcard',
            'must' => $formSet['base']['idcard_must'] ? : 0,
            'value' => $member['idcard'],
            'show' => (bool)$formSet['base']['idcard']
        ];
        $returnData[] = [
            'name' => '身份证地址',
            'field' => 'idcard_addr',
            'must' => $formSet['base']['idcard_addr_must'] ? : 0,
            'value' => $member['idcard_addr'],
            'show' => (bool)$formSet['base']['idcard_addr']
        ];
        return $returnData;
    }

    private function fixedDiyField($member)
    {
        $memberSet = Setting::get('shop.member');
        if (!$memberSet['is_custom']) {
            return [];
        }
        return [
            [
                'name' => $memberSet['custom_title'] ? : "",
                'field' => 'custom_value',
                'must' => 1,
                'value' => $member['yz_member']['custom_value'] ? : '',
            ]
        ];
    }

    private function diyField($member)
    {
        $formSet = json_decode(Setting::get('shop.form'),true);
        $form = array_values(array_sort($formSet['form'], function ($value) {
            return $value['sort'];
        }));
        $returnData = [
            'form_edit' => $formSet['base']['form_edit'] ? 1 : 0,
            'form_open' => $formSet['base']['form_open'] ? 1 : 0,
            'form' => []
        ];
        $member_form = $member['yz_member']['member_form']?json_decode($member['yz_member']['member_form'],true):[];
        $member_form = collect($member_form);
        foreach ($form as $item) {
            $has = $member_form->where('pinyin',$item['pinyin'])->first();
            $returnData['form'][] = [
                'name' => $item['name'],
                'field' => $item['pinyin'],
                'value' => $has ? $has['value'] : '',
            ];
        }
        return $returnData;
    }

    private function diyForm($member)
    {
        $memberSet = Setting::get('shop.member');
        if (!app('plugins')->isEnabled('diyform') || !$memberSet['form_id']) {
            return [];
        }
        $data = \Yunshop\Diyform\api\DiyFormController::getDiyFormById(request(), true,  $memberSet['form_id']);
        return $data['json'] ? : [];
    }

    public function userInfoSave()
    {
        try {
            list($data,$sub_data) = $this->userInfoVerify(request()->all());
            $member_id = \YunShop::app()->getMemberId();
            $member = Member::find($member_id);
            $yz_member = MemberShopInfo::where('member_id',$member_id)->first();
            \Log::debug('---会员更新基本资料---',[$data,$sub_data,request()->type]);
            $member->fill($data);
            $member->realname = $data['realname'];
            $member->birthyear = $data['birthyear'];
            $member->birthmonth = $data['birthmonth'];
            $member->birthday = $data['birthday'];
            $member->idcard = $data['idcard'];
            $member->idcard_addr = $data['idcard_addr'];

            $yz_member->fill($sub_data);
            if (!$member->save() || !$yz_member->save()) {
                throw new \Exception('保存失败');
            }
            if (Cache::has($member->uid . '_member_info')) {
                Cache::forget($member->uid . '_member_info');
            }
            return $this->successJson('保存成功');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 注册信息验证
     * @param $request
     * @return array[]
     * @throws \Exception
     */
    private function userInfoVerify($request)
    {
        $formSet = json_decode(Setting::get('shop.form'),true);
        //基础信息-注册填写
        if ($formSet && $formSet['base']['name'] && $formSet['base']['name_must'] && !$request['name']) {
            throw new \Exception('请填写姓名');
        }
        if ($formSet && $formSet['base']['sex'] && $formSet['base']['sex_must'] && !$request['gender']) {
            throw new \Exception('请填写性别');
        }
        if ($formSet && $formSet['base']['address'] && $formSet['base']['address_must'] && !$request['address']) {
            throw new \Exception('请填写详细地址');
        }
        if ($formSet && $formSet['base']['birthday'] && $formSet['base']['birthday_must'] && !$request['birthday']) {
            throw new \Exception('请填写生日');
        }
        if ($formSet && $formSet['base']['idcard'] && $formSet['base']['idcard_must'] && !$request['idcard']) {
            throw new \Exception('请填写身份证');
        }
        if ($formSet && $formSet['base']['idcard_addr'] && $formSet['base']['idcard_addr_must'] && !$request['idcard_addr']) {
            throw new \Exception('请填写身份证地址');
        }

        $memberSet = Setting::get('shop.member');
        //自定义字段-固定
        $custom_value = $request['custom_value'] ? : '';
        if (!$memberSet['is_custom']) {
            $custom_value = '';
        }

        //自定义字段
        $member_form = $form = array_values(array_sort($formSet['form'],function ($value) {
            return $value['sort'];
        }));
        foreach ($form as $key => &$item) {
            $item['del'] = 1;
            $member_form[$key]['value'] = $request['customDatas'][$item['pinyin']];
            if ($formSet['base']['form_open'] && !$member_form[$key]['value']) {
                throw new \Exception('自定义字段必填');
            }
        }
        $formSet['form'] = $form;
        Setting::set('shop.form', json_encode($formSet));
        unset($item);
        $member_form = $member_form ? json_encode($member_form) : '';

        $birthday = $request['birthday'] ? explode('-', $request['birthday']) : [];

        $member = [
            'realname' => $request['name'] ? : '',
            'gender' => $request['gender'] ? : 0,
            'birthyear' => $birthday ? $birthday[0] : 0,
            'birthmonth' => $birthday ? $birthday[1] : 0,
            'birthday' => $birthday ? $birthday[2] : 0,
            'idcard' => $request['idcard'] ? : '',
            'idcard_addr' => $request['idcard_addr'] ? : '',
        ];
        if ($request['nickname']) {
            $member['nickname'] = $request['nickname'];
        }
        if ($request['avatar']) {
            $member['avatar'] = $request['avatar'];
        }

        //添加yz_member表
        $yz_member = [
            'member_form' => $member_form,
            'province' => $request['address']['province'] ? : '',
            'city' => $request['address']['city']?:'',
            'area' => $request['address']['area']?:'',
            'province_name' => $request['address']['province_name']?:'',
            'city_name' => $request['address']['city_name']?:'',
            'area_name' => $request['address']['area_name']?:'',
            'address' => $request['address']['address']?:'',
            'custom_value' => $custom_value,
        ];
        return [$member,$yz_member];
    }

    public function changeAlipayInfo()
    {
        $member_id = \YunShop::app()->getMemberId();
        $yz_member = MemberShopInfo::where('member_id',$member_id)->first();
        $old_data = [
            'alipay' => $yz_member->alipay,
            'alipayname' => $yz_member->alipayname,
            'type' => \YunShop::request()->type
        ];

        $new_data = [
            'alipay' => request()->alipay,
            'alipayname' => request()->alipayname,
            'type' => \YunShop::request()->type
        ];
        $this->saveLog($member_id,$old_data,$new_data);
        $yz_member->alipay = request()->alipay;
        $yz_member->alipayname = request()->alipayname;
        if (!$yz_member->save()) {
            return $this->errorJson('保存失败');
        }
        return $this->successJson('保存成功');
    }

    public function changeWechat()
    {
        $member_id = \YunShop::app()->getMemberId();
        $yz_member = MemberShopInfo::where('member_id',$member_id)->first();
        $old_data = [
            'wechat' => $yz_member->wechat,
            'type' => \YunShop::request()->type
        ];

        $new_data = [
            'wechat' => request()->wechat,
            'type' => \YunShop::request()->type
        ];
        $this->saveLog($member_id,$old_data,$new_data);
        $yz_member->wechat = request()->wechat;
        if (!$yz_member->save()) {
            return $this->errorJson('保存失败');
        }
        return $this->successJson('保存成功');
    }

    private function saveLog($member_id,$old_data,$new_data)
    {
        $data = [
            'uniacid' => \YunShop::app()->uniacid,
            'uid' => $member_id,
            'old_data' => serialize($old_data),
            'new_data' => serialize($new_data),
            'session_id' => session_id()
        ];
        MembershipInformationLog::create($data);
    }

    private function getCaptcha()
    {
        //增加验证码功能
        $status = \Setting::get('shop.sms.status');
        if ($status == 1) {
            $result['captcha'] = app('captcha')->create('default', true);
            $result['captcha']['status'] = $status;
        } else {
            $result['captcha']['status'] = $status;
        }
        return $result;
    }

    public function platformAgreement()
    {
        $shopSet = Setting::get('shop.shop');
        if (!$shopSet['is_agreement']) {
            return $this->errorJson('协议未开启');
        }
        $agreement = RichText::get('shop.agreement');
        $returnData = [
            'title'  => $shopSet['agreement_name'] ? : "平台用户协议",
            'content'  => $agreement ? : ""
        ];
        return $this->successJson('',$returnData);
    }

    public function registerAgreement()
    {
        //协议
        $protocol = Protocol::uniacid()->first();
        if (!$protocol->status) {
            return $this->errorJson('协议未开启');
        }
        $returnData = [
            'title'  => $protocol->title ? : "会员注册协议",
            'content'  => $protocol->content ? : ""
        ];
        return $this->successJson('',$returnData);
    }

    public function userNicknameSave()
    {
        try {
            $data['nickname'] = request()->nickname;
            $data['avatar'] = request()->avatar;
            if (!$data['nickname'] || !$data['avatar']) {
                throw new \Exception('昵称和头像必须填写');
            }

            $member_id = \YunShop::app()->getMemberId();
            $member = Member::find($member_id);
            \Log::debug('---会员更新基本资料昵称头像---',[$data]);
            $member->nickname = $data['nickname'];
            $member->avatar = $data['avatar'];
            if (!$member->save()) {
                throw new \Exception('保存失败');
            }
            if (Cache::has($member->uid . '_member_info')) {
                Cache::forget($member->uid . '_member_info');
            }
            return $this->successJson('保存成功');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }
}