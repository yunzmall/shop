<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022/2/15
 * Time: 9:49
 */

namespace app\backend\modules\balance\services;


use app\backend\modules\finance\models\BalanceRechargeRecords;
use app\backend\modules\member\models\Member;
use app\common\facades\Setting;
use app\common\models\finance\BalanceRechargeCheck;
use app\common\services\finance\BalanceChange;
use Illuminate\Support\Facades\DB;

class BalanceRechargeService
{
    private $balanceSet;

    /**
     * @var Member
     */
    private $member;

    private $data;

    /**
     * @var BalanceRechargeCheck
     */
    private $rechargeCheckLog;

    /**
     * @var BalanceRechargeRecords
     */
    private $rechargeModel;

    public function getBalanceSet()
    {
        if (!isset($this->balanceSet)) {
            $this->balanceSet = Setting::get('finance.balance');
        }
        return $this->balanceSet;
    }

    public function setMember($member_id)
    {
        if (!isset($member) || $member_id != $member->uid) {
            $this->member = Member::find($member_id);
            if (!$this->member) {
                throw new \Exception('会员信息未找到');
            }
        }
    }

    /**
     * @return Member
     * @throws \Exception
     */
    public function getMember()
    {
        if (!isset($this->member)) {
            throw new \Exception('会员模型未设置');
        }
        return $this->member;
    }

    /**
     * 充值余额审核是否开启
     * @return bool
     */
    public function chargeCheckOpen()
    {
        return $this->getBalanceSet()['charge_check_swich'] ? true : false;
    }

    /**
     * @param array $rechargeData
     * @return bool
     * @throws \Exception
     */
    public function rechargeStart($rechargeData = [])
    {
        if (!$rechargeData) {
            throw new \Exception('参数错误');
        }
        $this->data = $rechargeData;
        $this->fillChargeCheckLog();
        if (in_array($this->data['type'],[BalanceRechargeRecords::PAY_TYPE_SHOP])) {
            //需要审核，进行保存
            if (!$this->rechargeCheckLog->explain) {
                throw new \Exception('请填写充值说明');
            }
            if (mb_strlen($this->rechargeCheckLog->recharge_remark) > 50) {
                throw new \Exception('备注不能超过50个字');
            }
            if (!$this->rechargeCheckLog->save()) {
                throw new \Exception('审核数据保存失败');
            }
        }
//        $this->recharge();
        return true;
    }

    /**
     * 添加审核数据(还未保存)
     */
    private function fillChargeCheckLog()
    {
        $fill = [
            'uniacid'     => \YunShop::app()->uniacid,
            'member_id'   => $this->data['member_id'],      //充值会员ID
            'money'       => $this->data['money'],          //充值金额
            'type'        => $this->data['type'],           //充值类型
            'operator_id' => $this->data['operator_id'],    //操作者ID
            'operator'    => $this->data['operator'],       //操作者
            'source'      => $this->data['source'],         //充值来源
            'remark'      => $this->data['remark'] ? : '',  //备注
            'explain'     => $this->data['explain'] ? : '', //充值说明
            'enclosure'   => $this->data['enclosure'] ? : '', //附件
            'recharge_remark' => $this->data['recharge_remark'] ? : '', //充值填写的备注
        ];
        $this->rechargeCheckLog = new BalanceRechargeCheck();
        $this->rechargeCheckLog->fill($fill);
    }

    /**
     * 审核
     * @param $id
     * @param $status
     * @return bool
     * @throws \Exception
     */
    public function verifyChargeLog($id,$status)
    {
        if (!$id || !in_array($status,[1,2])) {
            throw new \Exception('参数错误');
        }
        $this->rechargeCheckLog = BalanceRechargeCheck::uniacid()->where('id',$id)->first();
        if (!$this->rechargeCheckLog) {
            throw new \Exception('未找到该审核数据');
        }
        if ($this->rechargeCheckLog->status != 0) {
            throw new \Exception('该记录状态无法审核');
        }
        $this->rechargeCheckLog->status = $status;
        DB::transaction(function () use ($status) {
            if (!$this->rechargeCheckLog->save()) {
                throw new \Exception('审核失败');
            }
            if ($this->rechargeCheckLog->status == 1) {//审核通过，进行充值
                $this->setMember($this->rechargeCheckLog->member_id);//设置会员模型
                $this->recharge();
            }
        });
        return true;
    }

    /**
     * @return \Illuminate\Support\MessageBag|string
     * @throws \Exception
     */
    private function recharge()
    {
        $this->rechargeModel = new BalanceRechargeRecords();
        $this->rechargeModel->fill($this->getRechargeData());
        $validator = $this->rechargeModel->validator();
        if ($validator->fails()) {
            throw new \Exception($validator->messages());
        }
        if ($this->rechargeModel->save()) {
            $data = $this->getChangeBalanceData();
            if ($this->rechargeModel->money > 0) {
                $data['change_value'] = $this->rechargeModel->money;
                $result = (new BalanceChange())->recharge($data);
            } else {
                $data['change_value'] = -$this->rechargeModel->money;
                $result = (new BalanceChange())->rechargeMinus($data);
            }
            if ($result !== true) {
                throw new \Exception($result);
            }
        }
        return true;
    }

    private function getChangeBalanceData()
    {
        return array(
            'member_id'     => $this->rechargeCheckLog->member_id,
            'remark'        => $this->rechargeCheckLog->remark,
            'source'        => $this->rechargeCheckLog->source,
            'relation'      => $this->rechargeModel->ordersn,
            'operator'      => $this->rechargeCheckLog->operator,
            'operator_id'   => $this->rechargeCheckLog->operator_id
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getRechargeData()
    {
        return array(
            'uniacid'       => $this->rechargeCheckLog->uniacid,
            'member_id'     => $this->rechargeCheckLog->member_id,
            'old_money'     => $this->getMember()->credit2,
            'money'         => $this->rechargeCheckLog->money,
            'new_money'     => $this->getNewMoney(),
            'type'          => $this->rechargeCheckLog->type,
            'ordersn'       => BalanceRechargeRecords::createOrderSn('RV','ordersn'),
            'status'        => BalanceRechargeRecords::PAY_STATUS_SUCCESS,
            'remark'        => $this->rechargeCheckLog->recharge_remark
        );
    }

    /**
     * @return int|string
     * @throws \Exception
     */
    private function getNewMoney()
    {
        $new_value = bcadd($this->getMember()->credit2, $this->rechargeCheckLog->money, 2);
        return $new_value > 0 ? $new_value : 0;
    }
}