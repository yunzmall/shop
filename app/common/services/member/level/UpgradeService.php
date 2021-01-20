<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/7/14 11:39 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\common\services\member\level;


use app\common\events\member\MemberLevelUpgradeEvent;
use app\common\models\MemberShopInfo;
use app\common\services\notice\official\MemberUpgradeNotice;

class UpgradeService
{
    public function upgrade($memberId, $upgradeLevel)
    {
        if (!$upgradeLevel || !$memberId) {
            return false;
        }
        $memberModel = MemberShopInfo::ofMemberId($memberId)->withLevel()->first();
        if (!$memberModel) {
            return false;
        }
        $oLevel = isset($this->memberModel->level->level) ?: 0;
        //验证等级权重
        if ($upgradeLevel->level > $oLevel) {
            $memberModel->level_id = $upgradeLevel->id;
            $memberModel->upgrade_at = time();

            $memberModel->save();

            event(new MemberLevelUpgradeEvent($memberModel, false));

            $memberNotice = new MemberUpgradeNotice($memberModel,$upgradeLevel);
            $memberNotice->sendMessage();

            return true;
        }
        return false;
    }


}
