<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/13
 * Time: 下午7:01
 */

namespace app\frontend\modules\finance\services;

use app\backend\modules\member\models\Member;
use app\common\exceptions\AppException;
use app\common\models\member\ChildrenOfMember;
use app\common\models\member\ParentOfMember;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\facades\Setting;
use app\frontend\modules\finance\models\Balance as BalanceCommon;
use app\frontend\modules\finance\models\BalanceRecharge;

class BalanceService
{
    private $_recharge_set;

    private $_withdraw_set;

    public function __construct()
    {
        $this->_recharge_set = Setting::get('finance.balance');
        $this->_withdraw_set = Setting::get('withdraw.balance');
    }

    /**
     * 余额首页数据
     * @return array
     */
    public function getIndexData()
    {
        $this->setButtonArray($index_data);
        $this->setRechargeActivity($index_data);
        $this->setOther($index_data);
        $index_data['balance_log'] = BalanceCommon::getThreeData();
        $index_data['balance'] = Member::where('uid', \YunShop::app()->getMemberId())->value('credit2');
        return $index_data;
    }

    /**
     * 余额首页的按钮
     * @param $index_data
     * @return void
     */
    private function setButtonArray(&$index_data)
    {
        $index_data['balance_button'] = [];
        $this->getRechargeData($index_data);
        $this->getTransferData($index_data);
        $this->getWithdrawData($index_data);
    }

    /**
     * 充值按钮
     * @return void
     */
    private function getRechargeData(&$index_data): void
    {
        if ($this->_recharge_set['recharge']) {
            $index_data['balance_button'][] = [
                'title' => '充值',
                'url'   => 'Balance_recharge'
            ];
        }
    }

    /**
     * 转账按钮
     * @return void
     */
    private function getTransferData(&$index_data): void
    {
        if ($this->_recharge_set['transfer']) {
            $index_data['balance_button'][] = [
                'title' => '转账',
                'url'   => 'balance_transfer'
            ];
        }
    }

    /**
     * 提现按钮
     * @return void
     */
    private function getWithdrawData(&$index_data): void
    {
        $name = Setting::get('shop.lang.zh_cn.income.name_of_withdrawal');
        if (Setting::get('withdraw.balance.status')) {
            $index_data['balance_button'][] = [
                'title' => empty($name) ? '提现' : $name,
                'url'   => 'balance_withdrawals'
            ];
        }
    }

    /**
     * 其他功能
     * @param $index_data
     * @return void
     */
    private function setOther(&$index_data): void
    {
        $index_data["other"] = [];
        //转化爱心值
        $this->getLove($index_data);
        if(empty($index_data['other'])){
            $index_data['other']=false;
        }
    }

    private function getLove(&$index_data)
    {
        //转化爱心值
        if ($this->_recharge_set['love_swich']) {
            $index_data["other"]["love"] = [
                'title' => LOVE_NAME,
                'img'   => 'https://mini-app-img-1251768088.cos.ap-guangzhou.myqcloud.com/images/balance/balanceLove@2x.png'
            ];
        }
    }

    /**
     * 活动数据
     * @return void
     */
    private function setRechargeActivity(&$index_data): void
    {
        $start = $this->_recharge_set['recharge_activity_start'];
        $end = $this->_recharge_set['recharge_activity_end'];
        $time_bool = (time() >= $start) && (time() <= $end);
        $index_data['recharge_activity'] = [];
        //开启活动 && 活动时间内 && 开启了充值 && 有活动内容
        if ($this->_recharge_set['recharge_activity'] && $time_bool && $this->_recharge_set['recharge'] && $this->_recharge_set['sale']) {
            $index_data['recharge_activity'] = [
                'activity' => $this->_recharge_set['sale'],//活动说明,
                'type'     => $this->_recharge_set['proportion_status']//充值返回类型（0固定数值/1比例）
            ];
        }
    }

    //余额设置接口
    public function getBalanceSet()
    {
        return array(
            'recharge'         => $this->_recharge_set['recharge'] ? 1 : 0,
            'transfer'         => $this->_recharge_set['transfer'] ? 1 : 0,
            'withdraw'         => $this->_withdraw_set['status'] ? 1 : 0,
            'withdrawToWechat' => $this->withdrawWechat(),
            'withdrawToAlipay' => $this->withdrawAlipay(),
            'withdrawToManual' => $this->withdrawManual(),
            'withdrawEup'      => $this->withdrawEup()
        );
    }

    //余额充值设置
    public function rechargeSet()
    {
        return $this->_recharge_set['recharge'] ? true : false;
    }

    //余额充值优惠
    public function rechargeSale()
    {
        return $this->rechargeSet() ? $this->_recharge_set['sale'] : [];
    }

    //0赠送固定金额，1赠送充值比例

    public function proportionStatus()
    {
        return isset($this->_recharge_set['proportion_status']) ? $this->_recharge_set['proportion_status'] : '0';
    }

    //余额转让设置
    public function transferSet()
    {
        return $this->_recharge_set['transfer'] ? true : false;
    }

    //余额转让设置
    public function teamTransferSet()
    {
        return $this->_recharge_set['team_transfer'] ? true : false;
    }

    //余额转化爱心值
    public function convertSet()
    {
        return $this->_recharge_set['love_swich'] ? true : false;
    }

    // 余额转化爱心值，为0或为空 按100计算
    public function convertRate()
    {
        return $this->_recharge_set['love_rate'] ?: 100;
    }

    //余额提现设置
    public function withdrawSet()
    {
        return $this->_withdraw_set['status'] ? true : false;
    }

    //余额提现限额设置
    public function withdrawAstrict()
    {
        return $this->_withdraw_set['withdrawmoney'] ?: '0';
    }

    //余额提现倍数限制
    public function withdrawMultiple()
    {
        return $this->_withdraw_set['withdraw_multiple'];
    }

    //余额提现手续费
    public function withdrawPoundage()
    {
        return $this->_withdraw_set['poundage'] ?: '0';
    }

    //余额提现到微信
    public function withdrawWechat()
    {
        return $this->_withdraw_set['wechat'] ? true : false;
    }

    //余额提现到微信限制
    public function withdrawWechatLimit()
    {
        $wechat_min = $this->_withdraw_set['wechat_min'];
        $wechat_max = $this->_withdraw_set['wechat_max'];
        $wechat_frequency = $this->_withdraw_set['wechat_frequency'];
        $data = [
            'wechat_min'       => $wechat_min,
            'wechat_max'       => $wechat_max,
            'wechat_frequency' => $wechat_frequency,
        ];
        return $data;
    }

    //余额提现到支付寶限制
    public function withdrawAlipayLimit()
    {
        $alipay_min = $this->_withdraw_set['alipay_min'];
        $alipay_max = $this->_withdraw_set['alipay_max'];
        $alipay_frequency = $this->_withdraw_set['alipay_frequency'];
        $data = [
            'alipay_min'       => $alipay_min,
            'alipay_max'       => $alipay_max,
            'alipay_frequency' => $alipay_frequency,
        ];
        return $data;
    }

    //余额提现到好灵工-支付宝限制
    public function withdrawWorkerWithdrawAlipayLimit()
    {
        $data = [
            'worker_withdraw_alipay_min'       => $this->_withdraw_set['worker_withdraw_alipay_min'],
            'worker_withdraw_alipay_max'       => $this->_withdraw_set['worker_withdraw_alipay_max'],
            'worker_withdraw_alipay_frequency' => $this->_withdraw_set['worker_withdraw_alipay_frequency'],
        ];
        return $data;
    }

    //余额提现到好灵工-微信限制
    public function withdrawWorkerWithdrawWechatLimit()
    {
        $data = [
            'worker_withdraw_wechat_min'       => $this->_withdraw_set['worker_withdraw_wechat_min'],
            'worker_withdraw_wechat_max'       => $this->_withdraw_set['worker_withdraw_wechat_max'],
            'worker_withdraw_wechat_frequency' => $this->_withdraw_set['worker_withdraw_wechat_frequency'],
        ];
        return $data;
    }

    //余额提现到好灵工-银行卡限制
    public function withdrawWorkerWithdrawBankLimit()
    {
        $data = [
            'worker_withdraw_bank_min'       => $this->_withdraw_set['worker_withdraw_bank_min'],
            'worker_withdraw_bank_max'       => $this->_withdraw_set['worker_withdraw_bank_max'],
            'worker_withdraw_bank_frequency' => $this->_withdraw_set['worker_withdraw_bank_frequency'],
        ];
        return $data;
    }

    //余额提现到智E+-银行卡限制
    public function withdrawEplusWithdrawBankLimit()
    {
        $data = [
            'eplus_withdraw_bank_min'       => $this->_withdraw_set['eplus_withdraw_bank_min'],
            'eplus_withdraw_bank_max'       => $this->_withdraw_set['eplus_withdraw_bank_max'],
            'eplus_withdraw_bank_frequency' => $this->_withdraw_set['eplus_withdraw_bank_frequency'],
        ];
        return $data;
    }


    //余额提现到支付宝
    public function withdrawAlipay()
    {
        return $this->_withdraw_set['alipay'] ? true : false;
    }

    //余额手动提现
    public function withdrawManual()
    {
        return $this->_withdraw_set['balance_manual'] ? true : false;
    }

    //余额EUP提现
    public function withdrawEup()
    {
        if (app('plugins')->isEnabled('eup-pay')) {
            return $this->_withdraw_set['eup_pay'] ? true : false;
        }
        return false;
    }

    //余额环迅提现
    public function withdrawHuanxun()
    {
        if (app('plugins')->isEnabled('huanxun')) {
            return $this->_withdraw_set['huanxun'] ? true : false;
        }
        return false;
    }

    //余额汇聚提现
    public function withdrawConverge()
    {
        if (app('plugins')->isEnabled('converge_pay')) {
            return $this->_withdraw_set['converge_pay'] ? true : false;
        }
        return false;
    }

    //余额高灯提现
    public function withdrawHighLight($withdrawType)
    {
        if (app('plugins')->isEnabled('high-light')) {
            return $this->_withdraw_set[$withdrawType] ? true : false;
        }
        return false;
    }

    /**
     * @return array|int[]
     * 余额提现额外数据
     */
    public function extraData()
    {
        $return_data = [];
        if (app('plugins')->isEnabled('worker-withdraw')) {
            $return_data['worker_withdraw'] = \Yunshop\WorkerWithdraw\services\SettingService::withdrawListExtraData();
        }
        if (app('plugins')->isEnabled('eplus-pay')) {
            $return_data['eplus_withdraw'] = \Yunshop\EplusPay\services\SettingService::withdrawListBankExtraData();
        }
        return $return_data;
    }


    public function eplusWithdrawEnable()
    {
        return $this->_withdraw_set['eplus_withdraw_bank'] && \Yunshop\EplusPay\services\SettingService::usable();
    }

    public function silverPointWithdrawEnable()
    {
        return $this->_withdraw_set['silver_point']
            && app('plugins')->isEnabled('silver-point-pay')
            && Setting::get('silver-point-pay.set.plugin_enable')
            && Setting::get('silver-point-pay.set.behalf_enable');
    }

    public function jianzhimaoBankWithdrawEnable()
    {
        return $this->_withdraw_set['jianzhimao_bank']
            && app('plugins')->isEnabled('jianzhimao-withdraw')
            && Setting::get('jianzhimao-withdraw.set.plugin_enable');
    }

    public function taxWithdrawBankEnable()
    {
        return $this->_withdraw_set['tax_withdraw_bank']
            && app('plugins')->isEnabled('tax-withdraw')
            && Setting::get('tax-withdraw.set.plugin_enable');
    }

    //帮扶中心核销
    public function supportCenterWithdrawEnable()
    {
        return app('plugins')->isEnabled('support-center') && \Yunshop\SupportCenter\models\SupportCenterConfigModel::getConfig('is_open');
    }

    //帮扶中心核销
    public function supportCenterWithdrawName()
    {
        return SUPPORT_CENTER_NAME ?: '帮扶中心';
    }

    /**
     * @param $withdrawType
     * @return bool
     * 好灵工余额提现是否可用
     */
    public function workerWithdrawEnable($withdrawType)
    {
        switch ($withdrawType) {
            case 'worker_withdraw_wechat':
                $re_type = 2;
                break;
            case 'worker_withdraw_alipay':
                $re_type = 1;
                break;
            case 'worker_withdraw_bank':
                $re_type = 1;
                break;
        }
        if ($this->_withdraw_set[$withdrawType] && app('plugins')->isEnabled(
                'worker-withdraw'
            ) && \Yunshop\WorkerWithdraw\services\SettingService::usable([], $re_type)) {
            return true;
        }
        return false;
    }

    /**
     * 提现满 N元 减免手续费 [注意为 0， 为空则不计算，按正常手续费扣]
     * 2017-09-28
     * @return string
     */
    public function withdrawPoundageFullCut()
    {
        return $this->_withdraw_set['poundage_full_cut'] ?: '0';
    }


    /**
     * 增加提现手续费类型，1固定金额，0（默认）手续费比例
     * 2017-09-28
     * @return int
     */
    public function withdrawPoundageType()
    {
        return $this->_withdraw_set['poundage_type'] ? 1 : 0;
    }

    public function rechargeActivityStatus()
    {
        return $this->_recharge_set['recharge_activity'] ? true : false;
    }

    public function rechargeActivityStartTime()
    {
        return $this->_recharge_set['recharge_activity_start'] ?: 0;
    }

    public function rechargeActivityEndTime()
    {
        return $this->_recharge_set['recharge_activity_end'] ?: 0;
    }

    public function rechargeActivityCount()
    {
        return $this->_recharge_set['recharge_activity_count'] ?: 1;
    }

    public function rechargeActivityFetter()
    {
        return $this->_recharge_set['recharge_activity_fetter'];
    }

    public function teamTransfer($recipient)
    {
        $parent_ids = [];
        $child_ids = [];
        $parent = ParentOfMember::uniacid()->where('member_id', \Yunshop::app()->getMemberId())->get();

        if (!$parent->isEmpty()) {
            $parent_ids = $parent->pluck('parent_id')->toArray();
        }

        $children = ChildrenOfMember::uniacid()->where('member_id', \Yunshop::app()->getMemberId())->get();
        if (!$children->isEmpty()) {
            $child_ids = $children->pluck('child_id')->toArray();
        }
        $ids = array_merge($parent_ids, $child_ids);
        if (in_array($recipient, $ids)) {
            return true;
        }

        return false;
    }
}
