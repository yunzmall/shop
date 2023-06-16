<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/6/19 下午1:51
 * Email: livsyitian@163.com
 */

namespace app\common\services\withdraw;


use app\common\events\withdraw\WithdrawFailedEvent;
use app\common\events\withdraw\WithdrawPayedEvent;
use app\common\events\withdraw\WithdrawPayEvent;
use app\common\events\withdraw\WithdrawPayingEvent;
use app\common\exceptions\ShopException;
use app\common\models\Income;
use app\common\models\Member;
use app\common\models\Withdraw;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\services\income\WithdrawIncomeService;
use app\common\services\PayFactory;
use Illuminate\Support\Facades\DB;
use app\common\services\finance\BalanceNoticeService;
use app\common\services\finance\MessageService;

class PayedService
{
    /**
     * @var Withdraw
     */
    private $withdrawModel;

    private $msg;

    public function __construct(Withdraw $withdrawModel)
    {
        $this->setWithdrawModel($withdrawModel);
    }

    public function withdrawPay()
    {
        if ($this->withdrawModel->status == Withdraw::STATUS_AUDIT) {
            $this->_withdrawPay();
            return true;
        }
        throw new ShopException("提现打款：ID{$this->withdrawModel->id}，不符合打款规则");
    }


    /**
     * 确认打款接口
     *
     * @return bool
     * @throws ShopException
     */
    public function confirmPay()
    {
        if ($this->withdrawModel->status == Withdraw::STATUS_PAYING || $this->withdrawModel->status == Withdraw::STATUS_AUDIT) {
            $this->withdrawModel->pay_at = time();
            try {
                DB::transaction(function () {
                    $this->_payed();
                });
                return true;
            } catch (\Exception $e) {
                if ($this->withdraw_set['free_audit'] == 1) {
                    event(new WithdrawFailedEvent($this->withdrawModel));
                    $this->sendMessage();
                }
            }
        }
        throw new ShopException('提现记录不符合确认打款规则');
    }


    /**
     * 提现打款
     *
     * @return bool
     * @throws ShopException
     */
    private function _withdrawPay()
    {
        try {
            DB::transaction(function () {
                $this->pay();
            });
            return $this->payed();
        } catch (\Exception $e) {
            if (\Setting::get('withdraw.income.free_audit') == 1) {
                event(new WithdrawFailedEvent($this->withdrawModel));
                $this->sendMessage();
            }
        }
        throw new ShopException($this->msg ?: '提现失败');
    }


    private function pay()
    {
        event(new WithdrawPayEvent($this->withdrawModel));
        //提现收入表
        if (!\YunShop::request()->again_pay) {
            WithdrawIncomeService::insert($this->withdrawModel);
        }
        $this->paying();
    }


    private function paying()
    {
        $this->withdrawModel->status = Withdraw::STATUS_PAYING;
        $this->withdrawModel->pay_at = time();
        event(new WithdrawPayingEvent($this->withdrawModel));
        \Log::debug('++++app_common_services_withdraw_PayService----paying---');
        $this->updateWithdrawModel();
    }


    private function payed()
    {
        $result = $this->tryPayed();
        if ($result === true) {
            DB::transaction(function () {
                $this->_payed();
            });
        }
        return true;
    }


    private function _payed()
    {
        $this->withdrawModel->status = Withdraw::STATUS_PAY;
        $this->withdrawModel->arrival_at = time();
        \Log::debug('---------eventmodel+++++++++-----------------');
        $this->updateWithdrawModel();

        event(new WithdrawPayedEvent($this->withdrawModel));
    }


    /**
     * 尝试打款
     *
     * @return bool
     * @throws ShopException
     */
    private function tryPayed()
    {
        try {
            $result = $this->_tryPayed();

            //dd($result);
            if ($result !== true) {
                //处理中 返回 false , 提现记录打款中
                return false;
            }
            return true;
        } catch (\Exception $exception) {
            $this->withdrawModel->status = Withdraw::STATUS_AUDIT;
            $this->withdrawModel->pay_at = null;
            WithdrawIncomeService::delete($this->withdrawModel);
            \Log::debug('++++app_common_services_withdraw_PayService----tryPayed---');
            $this->updateWithdrawModel();
            $this->msg = $exception->getMessage();
            throw new ShopException($this->msg);
        } finally {
            // todo 增加验证队列
        }
    }


    /**
     * 尝试打款
     *
     * @return bool
     * @throws ShopException
     */
    private function _tryPayed()
    {
        switch ($this->withdrawModel->pay_way) {
            case Withdraw::WITHDRAW_WITH_BALANCE:
                $result = $this->balanceWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_WECHAT:
                $result = $this->wechatWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_ALIPAY:
                $result = $this->alipayWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_MANUAL:
                $result = $this->manualWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_HUANXUN:
                $result = $this->huanxunWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_EUP_PAY:
                $result = $this->eupWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_SEPARATE_UNION_PAY:
                $result = $this->separateUnionPay();
                break;
            case Withdraw::WITHDRAW_WITH_YOP:
                $result = $this->yopWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_CONVERGE_PAY:
                $result = $this->convergePayWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_YEE_PAY:
                $result = $this->yeePayWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_HIGH_LIGHT_WECHAT:
                $result = $this->highLightWithdrawPay(Withdraw::WITHDRAW_WITH_HIGH_LIGHT_WECHAT);
                break;
            case Withdraw::WITHDRAW_WITH_HIGH_LIGHT_ALIPAY:
                $result = $this->highLightWithdrawPay(Withdraw::WITHDRAW_WITH_HIGH_LIGHT_ALIPAY);
                break;
            case Withdraw::WITHDRAW_WITH_HIGH_LIGHT_BANK:
                $result = $this->highLightWithdrawPay(Withdraw::WITHDRAW_WITH_HIGH_LIGHT_BANK);
                break;
            case Withdraw::WITHDRAW_WITH_WORK_WITHDRAW_ALIPAY:
            case Withdraw::WITHDRAW_WITH_WORK_WITHDRAW_BANK:
            case Withdraw::WITHDRAW_WITH_WORK_WITHDRAW_WECHAT:
                $result = $this->workerWithdrawPay($this->withdrawModel->pay_way);
                break;
            case Withdraw::WITHDRAW_WITH_EPLUS_WITHDRAW_BANK:
                $result = $this->eplusWithdrawPay($this->withdrawModel->pay_way);
                break;
            case Withdraw::WITHDRAW_WITH_SILVER_POINT:
                $result = $this->silverPointWithdrawPay();
                break;
            case Withdraw::WITHDRAW_WITH_JIANZHIMAO_BANK:
                $result = $this->jianzhimaoWithdrawPay();
                break;
            case Withdraw::TAX_WITHDRAW_BANK:
                $result = $this->taxWithdrawPay();
                break;
            default:
                $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：未知打款类型";
                throw new ShopException($this->msg);
        }
        return $result;
    }


    /**
     * 提现打款：余额打款
     *
     * @return bool
     * @throws ShopException
     */
    private function balanceWithdrawPay()
    {
        $remark = "提现打款-{$this->withdrawModel->type_name}-金额:{$this->withdrawModel->actual_amounts}";

        $data = array(
            'member_id'    => $this->withdrawModel->member_id,
            'remark'       => $remark,
            'source'       => ConstService::SOURCE_INCOME,
            'relation'     => '',
            'operator'     => ConstService::OPERATOR_MEMBER,
            'operator_id'  => $this->withdrawModel->id,
            'change_value' => $this->withdrawModel->actual_amounts
        );

        $result = (new BalanceChange())->income($data);

        if ($result !== true) {
            $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：{$result}";
            throw new ShopException($this->msg);
        }
        return true;
    }


    /**
     * 提现打款：微信打款
     *
     * @return bool
     * @throws ShopException
     */
    private function wechatWithdrawPay()
    {
        $memberId = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = $this->withdrawModel->actual_amounts;
        $remark = '';


        $memberModel = Member::uniacid()->where('uid', $memberId)->with(['hasOneFans', 'hasOneMiniApp', 'hasOneWechat']
        )->first();

        //优先使用微信会员打款
        if ($memberModel->hasOneFans->openid) {
            $result = PayFactory::create(PayFactory::PAY_WEACHAT)->doWithdraw($memberId, $sn, $amount, $remark, 1);
            //微信会员openid不存在时，假设使用小程序会员openid
        } elseif (app('plugins')->isEnabled('min-app') && $memberModel->hasOneMiniApp->openid) {
            $result = PayFactory::create(PayFactory::PAY_WE_CHAT_APPLET)->doWithdraw(
                $memberId,
                $sn,
                $amount,
                $remark,
                1
            );
        } elseif (app('plugins')->isEnabled('app-set') && $memberModel->hasOneWechat->openid) {
            $result = PayFactory::create(PayFactory::PAY_WE_CHAT_APP)->doWithdraw($memberId, $sn, $amount, $remark, 1);
        } else {
            event(new WithdrawFailedEvent($this->withdrawModel));
            $this->sendMessage('提现会员openid错误');
            throw new ShopException("收入提现ID：{$this->withdrawModel->id}，提现失败：提现会员openid错误");
        }
        if ($result['errno'] == 1) {
            event(new WithdrawFailedEvent($this->withdrawModel));
            $this->sendMessage();
            throw new ShopException("收入提现ID：{$this->withdrawModel->id}，提现失败：{$result['message']}");
        }

        $v3_switch = false;
        if ($memberModel->hasOneFans->openid) {
            $income_set = \Setting::get('shop.pay');
            $v3_switch = (bool)$income_set['weixin_apiv3'];
        } elseif (app('plugins')->isEnabled('min-app') && $memberModel->hasOneMiniApp->openid) {
            $appletSet = \Setting::get('plugin.min_app');
            $v3_switch = (bool)$appletSet['v3_switch'];
        } elseif (app('plugins')->isEnabled('app-set') && $memberModel->hasOneWechat->openid) {
            $appSet = \Setting::get('shop_app.pay');
            $v3_switch = (bool)$appSet['weixin_v3'];
        }
        if ($v3_switch) {
            return false;//使用新版V3接口,保持打款中
        }

        return true;
    }


    private function alipayWithdrawPay()
    {
        $member_id = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = $this->withdrawModel->actual_amounts;
        $remark = '';

        $result = PayFactory::create(PayFactory::PAY_ALIPAY)->doWithdraw($member_id, $sn, $amount, $remark);
        \Log::debug('app_common_services_withdraw_PayService_in_alipay----result+++++', $result);

        if (is_array($result)) {
            if ($result['errno'] == 1) {
                $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：{$result['message']}";
                throw new ShopException($this->msg);
            }
            return true;
        }

        redirect($result)->send();
    }


    private function huanxunWithdrawPay()
    {
        $member_id = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = $this->withdrawModel->actual_amounts;
        $remark = '';

        $result = PayFactory::create(PayFactory::PAY_Huanxun_Quick)->doWithdraw($member_id, $sn, $amount, $remark);
        \Log::debug('app_common_services_withdraw_PayService_in_huanxun----result+++++', $result);

        if ($result['result'] == 10) {
            return true;
        }
        if ($result['result'] == 8) {
            return false;
        }
        $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：{$result['msg']}";
        throw new ShopException($this->msg);
    }


    private function eupWithdrawPay()
    {
        $member_id = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = $this->withdrawModel->actual_amounts;
        $remark = '';

        $result = PayFactory::create(PayFactory::PAY_EUP)->doWithdraw($member_id, $sn, $amount, $remark);
        \Log::debug('app_common_services_withdraw_PayService_in_eup----result+++++', $result);

        if ($result['errno'] === 0) {
            return true;
        }
        $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：{$result['message']}";
        throw new ShopException($this->msg);
    }

    private function yopWithdrawPay()
    {
        $member_id = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = $this->withdrawModel->actual_amounts;
        $remark = 'withdraw';

        $result = PayFactory::create(PayFactory::YOP)->doWithdraw($member_id, $sn, $amount, $remark);
        \Log::debug('app_common_services_withdraw_PayService_in_yop----result+++++', $result);

        if ($result['errno'] == 200) {
            return false;
        }
        $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：{$result['message']}";
        throw new ShopException($this->msg);
    }

    private function yeePayWithdrawPay()
    {
        $member_id = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = $this->withdrawModel->actual_amounts;
        $remark = 'withdraw';

        $result = PayFactory::create(PayFactory::YEE_PAY)->doWithdraw($member_id, $sn, $amount, $remark);
        \Log::debug('app_common_services_withdraw_PayService_in_yee_pay----result+++++', $result);

        if ($result['errno'] == 200) {
            return false;
        }
        $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：{$result['message']}";
        throw new ShopException($this->msg);
    }

    private function eplusWithdrawPay($type)
    {
        $member_id = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = bcadd($this->withdrawModel->actual_amounts, 0, 2);
        $remark = 'withdraw';

        $result = PayFactory::create(PayFactory::EPLUS_WECHAT_PAY)->doWithdraw(
            $member_id,
            $sn,
            $amount,
            $remark,
            $type
        );
        \Log::debug('app_common_services_withdraw_PayService_in_' . $type . '----result+++++', $result);

        if ($result['errno'] == 200) {
            return false;
        }
        $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：{$result['message']}";
        throw new ShopException($this->msg);
    }

    private function silverPointWithdrawPay()
    {
        $result = PayFactory::create(PayFactory::SILVER_POINT_PAYMENT)->doWithdraw(
            $this->withdrawModel->member_id,
            $this->withdrawModel->withdraw_sn,
            bcadd($this->withdrawModel->actual_amounts, 0, 2),
            'withdraw',
            $this->withdrawModel->pay_way
        );
        if ($result['errno'] == 200) {
            return false;
        }
        //银典支付要根据回调地址验证打款是否成功
    }

    private function jianzhimaoWithdrawPay()
    {
        $result = PayFactory::create(PayFactory::JIANZHIMAO_BANK)->doWithdraw(
            $this->withdrawModel->member_id,
            $this->withdrawModel->withdraw_sn,
            bcadd($this->withdrawModel->actual_amounts, 0, 2),
            'withdraw',
            $this->withdrawModel->pay_way
        );
        if ($result['errno'] == 200) {
            return false;
        }
        // 兼职猫要根据回调地址验证打款是否成功
    }

    private function taxWithdrawPay()
    {
        $result = PayFactory::create(PayFactory::TAX_WITHDRAW_BANK)->doWithdraw(
            $this->withdrawModel->member_id,
            $this->withdrawModel->withdraw_sn,
            bcadd($this->withdrawModel->actual_amounts, 0, 2),
            'withdraw',
            $this->withdrawModel->pay_way
        );
        if ($result['errno'] == 200) {
            return false;
        }
        //税筹提现要根据回调地址验证打款是否成功
    }

    private function workerWithdrawPay($type)
    {
        $member_id = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = bcadd($this->withdrawModel->actual_amounts, 0, 2);
        $remark = 'withdraw';

        $result = PayFactory::create(PayFactory::WORK_WITHDRAW_PAY)->doWithdraw(
            $member_id,
            $sn,
            $amount,
            $remark,
            $type
        );
        \Log::debug('app_common_services_withdraw_PayService_in_' . $type . '----result+++++', $result);

        if ($result['errno'] == 200) {
            return false;
        }
        $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：{$result['message']}";
        throw new ShopException($this->msg);
    }

    private function highLightWithdrawPay($type)
    {
        $member_id = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = $this->withdrawModel->actual_amounts;
        $remark = 'withdraw';

        $result = PayFactory::create(PayFactory::HIGH_LIGHT)->doWithdraw($member_id, $sn, $amount, $remark, $type);
        \Log::debug('app_common_services_withdraw_PayService_in_' . $type . '----result+++++', $result);

        if ($result['errno'] == 200) {
            return false;
        }
        $this->msg = "收入提现ID：{$this->withdrawModel->id}，提现失败：{$result['message']}";
        throw new ShopException($this->msg);
    }

    private function convergePayWithdrawPay()
    {
        $member_id = $this->withdrawModel->member_id;
        $sn = $this->withdrawModel->withdraw_sn;
        $amount = $this->withdrawModel->actual_amounts;
        $remark = 'withdraw';

        $result = PayFactory::create(PayFactory::PAY_WECHAT_HJ)->doWithdraw($member_id, $sn, $amount, $remark);

        if ($result['verify']) {
            return false;
        }
        $this->msg = "收入提现ID：{$this->withdrawModel->id}，汇聚提现失败：{$result['msg']}";
        throw new ShopException($this->msg);
    }


    private function separateUnionPay()
    {
        \Log::debug('--------尝试打款withdrawPay---------');
        $member_id = $this->withdrawModel->member_id;
        $withdraw_id = $this->withdrawModel->id;
        $amount = $this->withdrawModel->amounts;

        $sn = $this->withdrawModel->separate['order_sn'];
        $trade_no = $this->withdrawModel->separate['trade_no'];
        //如果订单号不存在或支付单号不存在 重新获取 服务重新打款功能
        if (app('plugins')->isEnabled('separate') && (!$sn || !$trade_no)) {
            $incomeId = $this->withdrawModel->type_id;

            $incomeRelationModel = \Yunshop\Separate\Common\Models\IncomeRelationModel::whereIncomeId($incomeId)->first(
            );

            $sn = $incomeRelationModel->order_sn;
            $trade_no = $incomeRelationModel->pay_order_sn;
        }

        \Log::debug('--------withdrawPay1---------$member_id', print_r($member_id, 1));
        //\Log::debug('--------withdrawPay2---------$sn', print_r($sn,1));
        //\Log::debug('--------withdrawPay3---------$withdraw_id', print_r($withdraw_id,1));
        \Log::debug('--------withdrawPay4---------$amount', print_r($amount, 1));
        //\Log::debug('--------withdrawPay5---------$trade_no', print_r($trade_no,1));
        //调用分帐接口
        $result = PayFactory::create(PayFactory::PAY_SEPARATE)->doWithdraw(
            $member_id,
            $sn,
            $amount,
            $withdraw_id,
            $trade_no
        );

        \Log::debug(
            '-------app_common_services_withdraw_PayService_in_separateUnionPay
            --withdrawPay---------$result',
            print_r($result, 1)
        );

        if ($result) {
            return true;
        }

        return false;
        //TODO  对接结果进行判断1
        //throw new ShopException("分账失败");
    }


    /**
     * 手动打款
     *
     * @return bool
     */
    private function manualWithdrawPay()
    {
        return true;
    }


    /**
     * @return bool
     * @throws ShopException
     */
    private function updateWithdrawModel()
    {
        \Log::debug('--------进入更新打款体现记录---------');
        $validator = $this->withdrawModel->validator();
        if ($validator->fails()) {
            \Log::debug('--------更新打款提现验证失败---------');
            $this->msg = $validator->messages();
            throw new ShopException($this->msg);
        }
        if (!$this->withdrawModel->save()) {
            $this->msg = "提现打款-打款记录更新状态失败";
            throw new ShopException($this->msg);
        }
        return true;
    }


    /**
     * @param $withdrawModel
     * @throws ShopException
     */
    private function setWithdrawModel($withdrawModel)
    {
        $this->withdrawModel = $withdrawModel;
    }


    private function sendMessage()
    {
        if ($this->withdrawModel->type == 'balance') {
            //余额提现失败通知
            BalanceNoticeService::withdrawFailureNotice($this->withdrawModel);
        } else {
            $ids = \Setting::get('withdraw.notice.withdraw_user');
            foreach ($ids as $k => $v) {
                (new MessageService($this->withdrawModel))->failureNotice($v['uid']);
            }
        }
    }
}
