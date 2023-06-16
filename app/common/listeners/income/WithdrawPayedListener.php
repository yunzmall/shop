<?php
/******************************************************************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2/9/22 3:11 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 *
 * 
 * 
 ******************************************************************************************************************/


namespace app\common\listeners\income;


use app\common\events\withdraw\WithdrawAuditedEvent;
use app\common\events\withdraw\WithdrawPayedEvent;
use app\common\facades\Setting;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use Illuminate\Contracts\Events\Dispatcher;

class WithdrawPayedListener
{
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen(WithdrawPayedEvent::class, static::class . "@withdrawPayed");
    }

    /**
     * #85481 提现奖励余额开发
     *
     * @param $event WithdrawPayedEvent
     */
    public function withdrawPayed($event)
    {
        if ($this->isAward()) $this->awardBalance($event->getWithdrawModel());
    }

    private function awardBalance($withdrawModel)
    {
        $amount = $withdrawModel->actual_amounts + $withdrawModel->actual_poundage + $withdrawModel->actual_servicetax;

        $awardValue = $this->awardValue($withdrawModel->pay_way, $amount);

        if ($awardValue > 0) {
            (new BalanceChange())->incomeWithdrawAward([
                'member_id'    => $withdrawModel->member_id,
                'change_value' => $awardValue,
                'remark'       => "收入提现奖励",
                'relation'     => "",
                'operator'     => ConstService::OPERATOR_SHOP,
                'operator_id'  => $withdrawModel->id
            ]);
        }
    }

    public function awardValue($payWay, $amount)
    {
        switch ($payWay) {
            case 'wechat':
                $rate = $this->weChatAwardRate();
                break;
            case 'high_light_bank':
            case 'high_light_wechat':
            case 'high_light_alipay':
                $rate = $this->lightAwardRate();
                break;
            default:
                $rate = $this->baseAwardRate();
        }
        return bcdiv(bcmul($amount, $rate, 4), 100, 2);
    }

    private function isAward()
    {
        return (bool)Setting::get('finance.balance.income_withdraw_award');
    }

    private function baseAwardRate()
    {
        return Setting::get('finance.balance.income_withdraw_award_rate');
    }

    private function weChatAwardRate()
    {
        return Setting::get('finance.balance.income_withdraw_wechat_rate') ?: $this->baseAwardRate();
    }

    private function lightAwardRate()
    {
        return Setting::get('finance.balance.income_withdraw_light_rate') ?: $this->baseAwardRate();
    }
}
