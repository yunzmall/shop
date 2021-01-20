<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/7/7 11:53 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\common\services\point;


use app\common\events\member\MemberBindMobile;
use app\common\facades\Setting;
use \app\common\models\point\BindMobileAward as BindMobileAwardModel;

class BindMobileAward
{
    /**
     * @param MemberBindMobile $event
     */
    public function award($event)
    {
        $memberModel = $event->getMemberModel();
        if ($this->awardIsRun()) {
            $this->awardMember($memberModel->uid);
        }
    }

    private function awardMember($memberId)
    {
        if (!BindMobileAwardModel::isAwarded($memberId)) {
            BindMobileAwardModel::awardMember($memberId, $this->awardPoint());
        }
    }

    /**
     * 是否运行开启绑定手机号奖励积分
     *
     * @return bool
     */
    private function awardIsRun()
    {
        return $this->awardState() && $this->awardPoint() > 0;
    }

    /**
     * 绑定手机号奖励积分值
     *
     * @return bool
     */
    private function awardPoint()
    {
        return Setting::get('point.set.bind_mobile_award_point');
    }

    /**
     * 绑定手机号奖励状态
     *
     * @return bool
     */
    private function awardState()
    {
        return !!Setting::get('point.set.bind_mobile_award');
    }
}
