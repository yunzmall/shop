<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/7 下午2:15
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/

namespace app\frontend\modules\finance\controllers;


use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\services\password\PasswordService;
use app\frontend\models\Member;

class PointPageController extends ApiController
{
    /**
     * @var Member
     */
    private $memberModel;

    public function index()
    {
        $this->getMemberInfo();

        if (!$this->memberModel) return $this->errorJson('未获取到会员信息');

        return $this->successJson('ok', $this->apiData());
    }

    private function apiData()
    {
        return [
            'credit1'       => $this->memberModel->credit1,
            'transfer'      => $this->getTransferStatus(),
            'activity'      => $this->getActivityStatus(),
            'rate'          => $this->getRateSet(),
            'lan_plugin'    => $this->lanPlugin(),
            'lan_name'      => $this->lanName(),
            'has_password'  => $this->hasPassword(),
            'need_password' => $this->needPassword()
        ];
    }

    private function lanPlugin()
    {
        return (int)app('plugins')->isEnabled('point_exchange');
    }

    private function lanName()
    {
        $set = Setting::get('plugin.point-exchange');

        return empty($set['plugin_name']) ? "蓝牛积分" : $set['plugin_name'];
    }

    private function hasPassword()
    {
        return $this->memberModel->yzMember->hasPayPassword();
    }

    private function needPassword()
    {
        return (new PasswordService())->isNeed('point', 'transfer');
    }

    private function getTransferStatus()
    {
        return Setting::get('point.set.point_transfer') ? true : false;
    }

    private function getActivityStatus()
    {
        return app('plugins')->isEnabled('point-activity');
    }

    private function getMemberInfo()
    {
        return $this->memberModel = Member::where('uid', $this->getMemberId())->first();
    }

    private function getMemberId()
    {
        return \YunShop::app()->getMemberId();
    }

    private function getRateSet()
    {
        return intval(Setting::get('point.set.point_transfer_poundage')) / 100 ?: 0;
    }
}
