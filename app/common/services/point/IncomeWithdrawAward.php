<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/5/25 2:19 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:
 ****************************************************************/


namespace app\common\services\point;


use app\common\events\withdraw\WithdrawPayedEvent;
use app\common\facades\Setting;
use app\common\models\Withdraw;
use app\common\services\finance\PointService;

class IncomeWithdrawAward
{
    /**
     * @var Withdraw
     */
    private $withdrawModel;


    /**
     * 收入提现，奖励手续费等值积分
     *
     * @param WithdrawPayedEvent $event
     */
    public function award($event)
    {
        $this->withdrawModel = $event->getWithdrawModel();
        if ($this->awardStatus()) {
            $this->awardPoint();
        }
    }

    private function awardPoint()
    {
        $data = [
            'point_income_type' => PointService::POINT_INCOME_GET,
            'point_mode'        => PointService::POINT_INCOME_WITHDRAW_AWARD,
            'member_id'         => $this->withdrawModel->member_id,
            'point'             => $this->withdrawModel->actual_poundage,
            'remark'            => "收入提现奖励积分[ID:{$this->withdrawModel->id}]",
        ];
        (new PointService($data))->changePoint();
    }

    /**
     * 收入提现，奖励比例积分
     *
     * @param WithdrawPayedEvent $event
     */
    public function awardScale($event)
    {
        $this->withdrawModel = $event->getWithdrawModel();
        if ($this->awardScaleStatus()) {
            $this->awardScalePoint();
        }
    }

    private function awardScalePoint()
    {
        $scale_point =  Setting::get('point.set.income_withdraw_award_scale_point');
        \Log::info('积分设置比例',$scale_point);
        if($scale_point){
            $amounts = round((($scale_point * ($this->withdrawModel->actual_amounts+$this->withdrawModel->actual_poundage+$this->withdrawModel->actual_servicetax))/100),2).'%';
            $data = [
                'point_income_type' => PointService::POINT_INCOME_GET,
                'point_mode'        => PointService::POINT_INCOME_WITHDRAW_AWARD_SCALE,
                'member_id'         => $this->withdrawModel->member_id,
                'point'             => $amounts,
                'remark'            => "收入提现奖励比例积分[ID:{$this->withdrawModel->id}]",
            ];
            (new PointService($data))->changePoint();
        }
    }



    private function awardStatus()
    {
        return !!Setting::get('point.set.income_withdraw_award');
    }

    private function awardScaleStatus()
    {
        return !!Setting::get('point.set.income_withdraw_award_scale');
    }
}
