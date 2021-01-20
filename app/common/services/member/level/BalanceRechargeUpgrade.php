<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/7/14 11:03 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\common\services\member\level;


use app\common\events\balance\RechargeSuccessEvent;
use app\common\facades\Setting;
use app\common\models\finance\BalanceRecharge;
use app\common\models\MemberLevel;

class BalanceRechargeUpgrade
{
    /**
     * @var BalanceRecharge
     */
    protected $rechargeModel;

    /**
     * @param RechargeSuccessEvent $event
     */
    public function checkUpgrade($event)
    {
        $this->rechargeModel = $event->getRechargeModel();

        if ($this->upgradeSet() == 4 && $upgradeLevel = $this->upgradeLevel()) {
            (new UpgradeService())->upgrade($this->rechargeModel->member_id, $upgradeLevel);
        }
    }

    //通过等级权重，取出满足条件最高权重的等级
    private function upgradeLevel()
    {
        return MemberLevel::where('balance_recharge', '<=', $this->rechargeModel->money)->orderBy('level', 'desc')->first();
    }

    /**
     * 会员等级升级依据设置
     *
     * @return int
     */
    private function upgradeSet()
    {
        return Setting::get('shop.member.level_type');
    }
}
