<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/12/4 下午2:11
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:     
 ****************************************************************/

namespace app\backend\modules\finance\controllers;


use app\common\components\BaseController;
use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use app\common\helpers\Url;

class BalanceSetController extends BaseController
{
    private $balance_set;

    /**
     * 查看余额设置
     * @return string
     */
    public function see()
    {
        if (request()->ajax()) {
            !$this->balance_set && $this->setBalanceSet();

            if (!is_null(\app\common\modules\shop\ShopConfig::current()->get('love_form'))) {
                $class = array_get(\app\common\modules\shop\ShopConfig::current()->get('love_form'), 'class');
                $function = array_get(\app\common\modules\shop\ShopConfig::current()->get('love_form'), 'function');
                $ret = $class::$function();
            }
            $this->balance_set['level_limit'] = (int)$this->balance_set['level_limit'];
            $this->balance_set['group_type'] = (int)$this->balance_set['group_type'];

            $this->balance_set['recharge_activity_start'] = $this->balance_set['recharge_activity_start'] * 1000;
            $this->balance_set['recharge_activity_end'] = $this->balance_set['recharge_activity_end'] * 1000;
            return $this->successJson('ok', [
                'balance' => $this->balance_set,
                'is_open' => $ret ?: false,
                'high_light_open' => app('plugins')->isEnabled('high-light') ? 1 : 0,
//                'day_data' => $this->getDayData(),
                'love_name' => app('plugins')->isEnabled('love') ? LOVE_NAME : '爱心值',
                'memberLevels' => \app\backend\modules\member\models\MemberLevel::getMemberLevelList(),
                'group_type' => \app\common\models\MemberGroup::uniacid()->select('id', 'group_name')->get()
            ]);
        }

        return view('finance.balance.index');
    }

    /**
     * 返回一天24时，对应key +1, 例：1 => 0:00
     * @return array
     */
    private function getDayData()
    {
        $dayData = [];
        for ($i = 1; $i <= 23; $i++) {
            $dayData += [
                $i => "每天" . $i . ":00",
            ];
        }
        return $dayData;
    }

    /**
     *
     * @return mixed|string更新余额设置数据
     */
    public function store()
    {
        $request_data = $this->getPostValue();

        if (Setting::set('finance.balance', $request_data)) {
            (new \app\common\services\operation\BalanceSetLog(['old' => $this->balance_set, 'new' => $request_data], 'update'));
            return $this->successJson('余额基础设置保存成功');
        }

        return $this->see();
    }


    private function getPostValue()
    {
        $this->validate($this->rules(), request(), [], $this->customAttributes());

        $request_data = \YunShop::request()->balance;

        //$request_data['sale'] = $this->rechargeSale($request_data);
        $request_data['recharge_activity_start'] = (int) ($request_data['recharge_activity_time']['start'] / 1000);
        $request_data['recharge_activity_end'] = (int) ($request_data['recharge_activity_time']['end'] / 1000);

        //顺序不能打乱，需要判断是否重置重置活动
        $request_data['recharge_activity_count'] = $this->getRechargeActivityCount($request_data['recharge_activity']);
        $request_data['recharge_activity'] = ($request_data['recharge_activity'] >= 1) ? 1 : 0;

        unset($request_data['recharge_activity_time']);

        return $request_data;
    }


    /**
     * 余额基础设置，附值 $this->balance_set
     */
    private function setBalanceSet()
    {
        $this->balance_set = Setting::get('finance.balance') ?: $this->defaultSet();
        if ($this->balance_set['uid']) {
            $this->balance_set['member'] = \app\backend\modules\member\models\Member::select('uid', 'mobile', 'nickname', 'realname', 'avatar')->find($this->balance_set['uid'])->toArray();
        }
    }


    private function getRechargeActivityCount($recharge_activity_status)
    {
        $this->setBalanceSet();

        $activity_count = !empty($this->balance_set['recharge_activity_count']) ? $this->balance_set['recharge_activity_count'] : 1;

        if ($recharge_activity_status == 2) {
            $activity_count += 1;
        }
        return $activity_count;
    }


    /**
     * 处理充值赠送数据，满额赠送数据
     *
     * @param $data
     * @return array
     * @Author yitian
     */
    private function rechargeSale($data)
    {
        $sale = array();
        $array = is_array($data['enough']) ? $data['enough'] : array();
        foreach ($array as $key => $value) {
            $enough = trim($value);
            if ($enough) {
                $sale[] = array(
                    'enough' => trim($data['enough'][$key]),
                    'give' => trim($data['give'][$key])
                );
            }
        }

        foreach ($sale as $key => $item) {
            $this->validatorCustomRules($item, $this->saleRules(), [], $this->saleCustomAttributes());
        }
        return $sale;
    }


    private function validatorCustomRules($array, $rules, $messages, $customAttributes)
    {
        $validator = $this->getValidationFactory()->make($array, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            throw new ShopException($validator->errors()->first());
        }
    }


    private function saleRules()
    {
        return [
            'enough' => 'numeric|min:0',
            'give' => 'numeric|min:0',
        ];
    }


    private function saleCustomAttributes()
    {
        return [
            'enough' => "满足金额值",
            'give' => "赠送金额",
        ];
    }


    private function rules()
    {
        return [
            'balance.recharge' => 'required|numeric|regex:/^[01]$/',
            'balance.recharge_activity' => 'required|numeric|regex:/^[012]$/',
            'balance.recharge_activity_fetter' => 'required|numeric|integer|min:-1|max:99999999',
            'balance.recharge_activity_time' => '',
            'balance.proportion_status' => 'required|numeric|regex:/^[01]$/',
            'balance.transfer' => 'required|numeric|regex:/^[01]$/',
        ];
    }


    private function customAttributes()
    {
        return [
            'balance.recharge' => '开启充值',
            'balance.recharge_activity' => '充值活动',
            'balance.recharge_activity_fetter' => '会员参与充值活动次数',
            'recharge_activity_time.start' => '充值活动开始时间',
            'recharge_activity_time.end' => '充值活动开始时间',
            'balance.proportion_status' => '充值赠送类型',
            'balance.transfer' => '转让开关',
        ];
    }

    private function defaultSet()
    {
        return [
            'balance_deduct' => "0",
            'balance_deduct_rollback' => "0",
            'balance_message_type' => "0",
            'blance_floor' => "",
            'blance_floor_on' => "0",
            'charge_check_swich' => "0",
            'charge_reward_rate' => "0",
            'charge_reward_swich' => "0",
            'group_type' => 0,
            'income_withdraw_award' => "0",
            'income_withdraw_award_explain' => "",
            'income_withdraw_award_rate' => "",
            'income_withdraw_light_rate' => "",
            'income_withdraw_wechat_rate' => "",
            'level_limit' => 0,
            'love_rate' => "",
            'love_swich' => "",
            'money_max' => "",
            'proportion_status' => "0",
            'recharge' => "0",
            'recharge_activity' => 0,
            'recharge_activity_count' => 0,
            'recharge_activity_end' => 0,
            'recharge_activity_fetter' => "0",
            'recharge_activity_start' => 0,
            'recharge_activity_time' => ['start' => 0, 'end' => 0],
            'sale' => [],
            'sms_hour' => "0",
            'sms_hour_amount' => "0",
            'sms_send' => "0",
            'team_transfer' => "0",
            'transfer' => "0",
            'uids' => "",
        ];
    }
}
