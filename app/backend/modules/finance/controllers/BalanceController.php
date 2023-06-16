<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/30
 * Time: 下午3:56
 */

namespace app\backend\modules\finance\controllers;


use app\backend\modules\balance\controllers\RechargeController;
use app\backend\modules\finance\services\BalanceService;
use app\backend\modules\member\models\Member;
use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\common\models\finance\Balance;
use app\common\models\finance\BalanceRecharge;
use app\common\models\finance\BalanceTransfer;
use \app\backend\modules\finance\models\BalanceRecharge as BackendBalanceRecharge;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;

class BalanceController extends BaseController
{

    private $_member_model;

    private $_recharge_model;


    /**
     * 查看余额明细详情
     *
     * @return string
     * @Author yitian
     */
    public function lookBalanceDetail()
    {
        $id = \YunShop::request()->id;
        $detailModel = \app\common\models\finance\Balance::getDetailById($id);

        return view('finance.balance.look-detail', [
            'detailModel' => $detailModel,
            'pager'       => ''
        ])->render();
    }

    /**
     * 会员余额转让记录
     *
     * @return string
     * @Author yitian
     */
    public function transferRecord()
    {
        if (request()->ajax()) {
            $pageSize = 20;

            $records = BalanceTransfer::records();

            $search = \YunShop::request()->search;
            if ($search) {
                $records = $records->search($search);
            }

            $pageList = $records->orderBy('created_at', 'desc')->paginate($pageSize)->toArray();
            $shopSet = Setting::get('shop.member');
            foreach ($pageList['data'] as &$item) {
                if ($item['transfer_info']) {
                    $item['transfer_info']['avatar'] = $item['transfer_info']['avatar'] ? tomedia($item['transfer_info']['avatar']) : tomedia($shopSet['headimg']);
                    $item['transfer_info']['nickname'] = $item['transfer_info']['nickname'] ?:
                        ($item['recipient_info']['mobile'] ? substr($item['recipient_info']['mobile'], 0, 2) . '******' . substr($item['recipient_info']['mobile'], -2, 2) : '无昵称会员');
                } else {
                    $item['transfer_info'] = [
                        'avatar' => tomedia($shopSet['headimg']),
                        'nickname' => '该会员已被删除或者已注销',
                    ];
                }
                if ($item['recipient_info']) {
                    $item['recipient_info']['avatar'] = $item['recipient_info']['avatar'] ? tomedia($item['recipient_info']['avatar']) : tomedia($shopSet['headimg']);
                    $item['recipient_info']['nickname'] = $item['recipient_info']['nickname'] ?:
                        ($item['recipient_info']['mobile'] ? substr($item['recipient_info']['mobile'], 0, 2) . '******' . substr($item['recipient_info']['mobile'], -2, 2) : '无昵称会员');
                } else {
                    $item['recipient_info'] = [
                        'avatar' => tomedia($shopSet['headimg']),
                        'nickname' => '该会员已被删除或者已注销',
                    ];
                }
            }

            return $this->successJson('ok', [
                'tansferList' => $pageList,
                'search' => $search
            ]);
        }

        return view('finance.balance.transferRecord')->render();
    }

    private function rechargeStart()
    {
        $this->_recharge_model = new BalanceRecharge();

        $this->_recharge_model->fill($this->getRechargeData());
        $validator = $this->_recharge_model->validator();
        if ($validator->fails()) {
            return $validator->messages();
        }
        if ($this->_recharge_model->save()) {


            //$result = (new BalanceService())->changeBalance($this->getChangeBalanceData());
            $data = $this->getChangeBalanceData();

            if ($this->_recharge_model->money > 0) {
                $data['change_value'] = $this->_recharge_model->money;
                $result = (new BalanceChange())->recharge($data);
            } else {
                $data['change_value'] = -$this->_recharge_model->money;
                $result = (new BalanceChange())->rechargeMinus($data);
            }
            return $result === true ? $this->updateRechargeStatus() : $result;
        }
        return '充值记录写入出错，请联系管理员';
    }

    private function updateRechargeStatus()
    {
        $this->_recharge_model->status = BalanceRecharge::PAY_STATUS_SUCCESS;
        if ($this->_recharge_model->save()) {
            return true;
        }
        return '充值状态修改失败';
    }

    private function getMemberInfo()
    {
        return $this->_member_model = Member::getMemberInfoById(\YunShop::request()->member_id) ?: false;
    }

    //充值记录数据
    private function getRechargeData()
    {
        $rechargeMoney = trim(\YunShop::request()->num);
        return array(
            'uniacid'   => \YunShop::app()->uniacid,
            'member_id' => \YunShop::request()->member_id,
            'old_money' => $this->_member_model->credit2,
            'money'     => $rechargeMoney,
            'new_money' => $this->getNewMoney(),
            'type'      => BalanceRecharge::PAY_TYPE_SHOP,
            'ordersn'   => $this->getRechargeOrderSN(),
            'status'    => BalanceRecharge::PAY_STATUS_ERROR,
        );
    }

    //获取计算后的余额值
    private function getNewMoney()
    {
        $newMoney = $this->_member_model->credit2 + trim(\YunShop::request()->num);
        return $newMoney > 0 ? $newMoney : 0;
    }

    //生成充值订单号
    private function getRechargeOrderSN()
    {
        return BalanceRecharge::createOrderSn('RV', 'ordersn');
    }

    private function getChangeBalanceData()
    {
        $money = $this->_recharge_model->money > 0 ? $this->_recharge_model->money : -$this->_recharge_model->money;
        return array(
            'member_id'   => $this->_recharge_model->member_id,
            'remark'      => '后台充值' . $this->_recharge_model->money . "元",
            'source'      => ConstService::SOURCE_RECHARGE,
            'relation'    => $this->_recharge_model->ordersn,
            'operator'    => ConstService::OPERATOR_SHOP,
            'operator_id' => \YunShop::app()->uid
        );
    }

    /**
     * 余额充值菜单
     *
     * @return array
     * @Author yitian
     */
    private function getRechargeMenu()
    {
        return array(
            'title'        => '余额充值',
            'name'         => '粉丝',
            'profile'      => '会员信息',
            'old_value'    => '当前余额',
            'charge_value' => '充值金额',
            'type'         => 'balance'
        );
    }

    /**
     * 处理充值赠送数据，满额赠送数据
     *
     * @param $data
     * @return array
     * @Author yitian
     */
    private function rechargeSale($data)
    {
        $result = array();
        $sale = is_array($data['enough']) ? $data['enough'] : array();
        foreach ($sale as $key => $value) {
            $enough = trim($value);
            if ($enough) {
                $result[] = array(
                    'enough' => trim($data['enough'][$key]),
                    'give'   => trim($data['give'][$key])
                );
            }
        }
        return $result;
    }

}
