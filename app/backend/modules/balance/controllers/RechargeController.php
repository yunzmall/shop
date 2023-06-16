<?php
/**
 * Created by PhpStorm.
 * User: king
 * Date: 2018/10/12
 * Time: 下午4:40
 */

namespace app\backend\modules\balance\controllers;


use app\backend\modules\balance\services\BalanceRechargeService;
use app\backend\modules\finance\models\BalanceRechargeRecords;
use app\backend\modules\member\models\Member;
use app\common\components\BaseController;
use app\common\events\finance\BalanceRechargedEvent;
use app\common\exceptions\ShopException;
use app\common\helpers\Url;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;

class RechargeController extends BaseController
{
    /**
     * @var Member
     */
    private $memberModel;

    /**
     * @var BalanceRechargeRecords
     */
    private $rechargeModel;



    public function index()
    {
        $this->memberModel = $this->getMemberModel();

        //todo 加速开发，暂时不提独立模型
        if (request()->ajax()) {
            if (!request()->num || !is_numeric(request()->num)) {
                return $this->errorJson('请输入充值金额');
            }
            $balanceRechargeService = new BalanceRechargeService();
            if ($balanceRechargeService->chargeCheckOpen()) {//需要进行审核
                try {
                    $balanceRechargeService->rechargeStart($this->getRechargeCheckData());
                    return $this->successJson('充值提交成功，等待审核通过即可完成充值');
                } catch (\Exception $e) {
                    return $this->errorJson($e->getMessage());
                }
            }

            $result = $this->rechargeStart();
            if ($result === true) {
                return $this->successJson('余额充值成功');
            }
            return $this->errorJson($result);
        }

        return view('finance.balance.recharge_new', $this->getResultData())->render();
    }

    /**
     * @return array
     */
    private function getResultData()
    {
        return [
            'rechargeMenu'  => $this->getRechargeMenu(),
            'memberInfo'    => $this->memberModel,
            'charge_check_swich' => \Setting::get('finance.balance.charge_check_swich') ? 1 : 0
        ];
    }

    private function rechargeStart()
    {
        $this->rechargeModel = new BalanceRechargeRecords();

        $this->rechargeModel->fill($this->getRechargeData());
        $validator = $this->rechargeModel->validator();
        if ($validator->fails()) {
            return $validator->messages();
        }
        if ($this->rechargeModel->save()) {
            $data = $this->getChangeBalanceData();
            if ($this->rechargeModel->money > 0 ) {
                $data['change_value'] = $this->rechargeModel->money;
                $result = (new BalanceChange())->recharge($data);
            } else {
                $data['change_value'] = -$this->rechargeModel->money;
                $result = (new BalanceChange())->rechargeMinus($data);
            }
            return $result === true ? $this->updateRechargeStatus() : $result;
        }
        return '充值记录写入出错，请联系管理员';
    }

    private function updateRechargeStatus()
    {
        $this->rechargeModel->status = BalanceRechargeRecords::PAY_STATUS_SUCCESS;
        if ($this->rechargeModel->save()) {
            event(new BalanceRechargedEvent($this->rechargeModel));
            return true;
        }
        return '充值状态修改失败';
    }

    private function getRechargeCheckData()
    {
        return array(
            'member_id'     => $this->memberModel->uid,
            'money'         => $this->getPostNum(),
            'type'          => BalanceRechargeRecords::PAY_TYPE_SHOP,
            'recharge_remark' => $this->getPostRemark(),
            'remark'        => '后台充值' . $this->getPostNum() . "元",
            'source'        => ConstService::SOURCE_RECHARGE,
            'operator'      => ConstService::OPERATOR_SHOP,
            'operator_id'   => \YunShop::app()->uid,
            'explain'       => trim(\YunShop::request()->explain),
            'enclosure'     => \YunShop::request()->enclosure,
        );
    }

    private function getChangeBalanceData()
    {
        return array(
            'member_id'     => $this->rechargeModel->member_id,
            'remark'        => '后台充值' . $this->rechargeModel->money . "元",
            'source'        => ConstService::SOURCE_RECHARGE,
            'relation'      => $this->rechargeModel->ordersn,
            'operator'      => ConstService::OPERATOR_SHOP,
            'operator_id'   => \YunShop::app()->uid
        );
    }

    /**
     * @return array
     */
    private function getRechargeData()
    {
        return array(
            'uniacid'       => \YunShop::app()->uniacid,
            'member_id'     => $this->memberModel->uid,
            'old_money'     => $this->memberModel->credit2,
            'money'         => $this->getPostNum(),
            'new_money'     => $this->getNewMoney(),
            'type'          => BalanceRechargeRecords::PAY_TYPE_SHOP,
            'ordersn'       => BalanceRechargeRecords::createOrderSn('RV','ordersn'),
            'status'        => BalanceRechargeRecords::PAY_STATUS_ERROR,
            'remark'        => $this->getPostRemark()
        );
    }

    /**
     * @return float
     */
    private function getNewMoney()
    {
        $new_value = bcadd( $this->memberModel->credit2, $this->getPostNum(), 2);
        return $new_value > 0 ? $new_value : 0;
    }

    /**
     * @return Member
     * @throws ShopException
     */
    private function getMemberModel()
    {
        $member_id = $this->getPostMemberId();

        $memberModel = Member::getMemberInfoById($member_id);
        if (!$memberModel) {
            throw new ShopException('会员信息错误');
        }
        return $memberModel;
    }

    /**
     * @return int
     * @throws ShopException
     */
    private function getPostMemberId()
    {
        $member_id = \YunShop::request()->member_id;
        if (!$member_id) {
            throw new ShopException('请输入正确的参数');
        }
        return (int)$member_id;
    }

    /**
     * @return float
     */
    private function getPostNum()
    {
        return trim(\YunShop::request()->num);
    }

    /**
     * @return string
     */
    private function getPostRemark()
    {
        return trim(\YunShop::request()->remark);
    }

    /**
     * @return array
     */
    private function getRechargeMenu()
    {
        return array(
            'title'         => '余额充值',
            'name'          => '粉丝',
            'profile'       => '会员信息',
            'old_value'     => '当前余额',
            'charge_value'  => '充值金额',
            'type'          => 'balance'
        );
    }
}
