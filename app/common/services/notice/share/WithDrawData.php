<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/21
 * Time: 18:21
 */

namespace app\common\services\notice\share;


use app\common\models\Member;
use app\common\models\Withdraw;

trait WithDrawData
{
    public $member;
    public $openid;
    public $withdrawModel;
    public $statusComment = [
        Withdraw::STATUS_INVALID => '审核无效',
        Withdraw::STATUS_INITIAL => '提现申请',
        Withdraw::STATUS_AUDIT   => '审核通过',
        Withdraw::STATUS_PAY     => '已打款',
        Withdraw::STATUS_REBUT   => '审核驳回',
        Withdraw::STATUS_PAYING  => '打款中',
    ];

    public function getWithdrawModel($withdraw)
    {
        $this->withdrawModel = $withdraw;
    }

    public function getMember()
    {
        $this->member = Member::uniacid()->where("uid",$this->withdrawModel->member_id)->first();
        $this->openid = $this->member['hasOneMiniApp']['openid'];
    }

    public function nickname()
    {
        return $this->withdrawModel->hasOneMember ? $this->withdrawModel->hasOneMember->nickname : '';
    }

    public function payWayName()
    {
        return $this->withdrawModel->pay_way_name;
    }

    public function getStatusComment($status)
    {
        return isset($this->statusComment[$status]) ? $this->statusComment[$status] : '';
    }
}