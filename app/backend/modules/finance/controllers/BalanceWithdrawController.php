<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/14
 * Time: 下午5:06
 */

namespace app\backend\modules\finance\controllers;


use app\common\events\withdraw\BalanceWithdrawSuccessEvent;
use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\helpers\Url;
use app\common\models\Member;
use app\common\models\Withdraw;
use app\backend\modules\finance\services\WithdrawService;
use app\common\components\BaseController;
use app\common\services\finance\BalanceNoticeService;
use app\common\services\Session;
use Illuminate\Support\Facades\Log;
use app\backend\modules\withdraw\controllers\AuditRejectedController;

class BalanceWithdrawController extends BaseController
{
    public $withdrawModel;


    public function detail()
    {
        if(request()->ajax()) {
            $this->withdrawModel = $this->attachedMode();
            $set = Setting::getByGroup('pay_password') ?: [];
            $shopSet = Setting::get('shop.member') ?? [];
            $shopSet['level_name'] = $shopSet['level_name'] ?? '普通会员';
            return $this->successJson('ok', [
                'item'          => $this->withdrawModel->toArray(),
                'is_verify'     => !empty($set['withdraw_verify']['is_phone_verify']) ? true : false,
                'expire_time'   => Session::get('withdraw_verify') ?: null,
                'verify_phone'  => $set['withdraw_verify']['phone'] ?: "",
                'verify_expire' => $set['withdraw_verify']['verify_expire'] ? intval(
                    $set['withdraw_verify']['verify_expire']
                ) : 10,
                'shopSet' => $shopSet,
            ]);
        }
        return view('finance.balance.withdraw')->render();
    }


    /**
     * @return mixed
     * @throws AppException
     */
    public function examine()
    {
        $requestData = \YunShop::request();

        $this->withdrawModel = $this->attachedMode();

        if (isset($requestData['submit_check'])) {
            //审核
            return $this->submitCheck();
        } elseif (isset($requestData['audited_rebut'])) {
            //审核后驳回
            return $this->auditedRebut();
        } elseif (isset($requestData['submit_cancel'])) {
            //重新审核
            return $this->submitCancel();
        } elseif (isset($requestData['submit_pay'])) {
            //打款
            return $this->submitPayNew();
        } elseif (isset($requestData['again_pay'])) {
            //重新打款
            $this->withdrawModel->status = 1;
            return $this->submitPayNew();
        } elseif (isset($requestData['confirm_pay'])) {
            //线下打款
            $this->withdrawModel->pay_at = time();
            $this->withdrawModel->status = 2;
            $this->withdrawModel->pay_way = 'manual';
            $this->withdrawUpdate();

            event(new BalanceWithdrawSuccessEvent($this->withdrawModel));
            BalanceNoticeService::withdrawSuccessNotice($this->withdrawModel);

            return $this->successJson('打款成功');
        }
        return $this->errorJson('提交数据有误，请刷新重试');
    }

    /**
     * 把打款方法剔出，临时使用
     */
    private function submitPayNew()
    {
        $check = $this->checkVerify();//打款验证
        if (!$check) {
            $this->errorJson(
                '提现验证失败或验证已过期', ['status' => -1]
            );
        }

        $result = $this->submitPay();

        if (!empty($result) && 0 == $result['errno']) {
            //todo 临时增加手动打款成功通知，重构时候注意优化
            if ($this->withdrawModel->pay_way == 'manual') {
                event(new BalanceWithdrawSuccessEvent($this->withdrawModel));
                BalanceNoticeService::withdrawSuccessNotice($this->withdrawModel);
            }
            return $this->successJson('提现申请成功', yzWebUrl('finance.balance-withdraw.detail', ['id' => \YunShop::request()->id]));
        }
        BalanceNoticeService::withdrawFailureNotice($this->withdrawModel);//提现失败通知
        $message = $result['message'] ?: '提现申请失败';
        return $this->errorJson($message, yzWebUrl('finance.balance-withdraw.detail', ['id' => \YunShop::request()->id]));
    }

    /**
     * 打款验证
     * @return bool
     */
    private function checkVerify()
    {
        $set = Setting::getByGroup('pay_password')['withdraw_verify'] ?: [];
        if (empty($set) || empty($set['is_phone_verify'])) {
            return true;
        }
        $verify = Session::get('withdraw_verify');  //没获取到
        if ($verify && $verify >= time()) {
            return true;
        }
        return false;
    }

    //提现审核
    private function submitCheck()
    {
        if ($this->withdrawModel->status != 0) {
            $this->errorJson('提交审核失败，该状态不能审核');
        }
        $this->withdrawModel->status = $this->getPostStatus();
        $this->withdrawModel->reject_reason = $this->withdrawModel->status != 1 ? trim(
            request()->reject_reason
        ) : '';//驳回理由
        $this->withdrawModel->audit_at = time();

        if ($this->getPostStatus() == -1) {
            BalanceNoticeService::withdrawFailureNotice($this->withdrawModel);
        }
        if ($this->getPostStatus() == 3) {
            return (new AuditRejectedController())->index();
            //BalanceNoticeService::withdrawRejectNotice($this->withdrawModel);
        }
        $this->withdrawUpdate();
        return $this->successJson('提交审核成功');
    }

    //审核后驳回
    private function auditedRebut()
    {
        return (new AuditRejectedController())->index();
    }


    /**
     * 提现重新审核
     * @return mixed
     */
    private function submitCancel()
    {
        return $this->submitCheck();
    }


    /**
     * 提现打款
     * @return mixed
     * @throws AppException
     */
    public function submitPay()
    {
        if ($this->withdrawModel->status !== 1) {
            throw new AppException('打款失败,数据不存在或不符合打款规则!');
        }

        $result = $this->payment();

        if (!empty($result) && 0 == $result['errno']) {
            $this->withdrawModel->pay_at = time();
            $this->withdrawModel->status = 2;

            $this->withdrawUpdate();
        } elseif (
            $this->withdrawModel->pay_way == 'alipay'
            || $this->withdrawModel->pay_way == 'yop_pay'
            || $this->withdrawModel->pay_way == 'silver_point'
        ) {
            $this->withdrawModel->pay_at = time();
            $this->withdrawModel->status = 4;

            $this->withdrawUpdate();
        }


        if (in_array($this->withdrawModel->pay_way, [
            'high_light_wechat',
            'high_light_alipay',
            'high_light_bank',
            'worker_withdraw_wechat',
            'worker_withdraw_alipay',
            'worker_withdraw_bank',
        ])) {
            if (!empty($result) && $result['errno'] == 200) {
//                $this->withdrawModel->pay_at = time();
                $this->withdrawModel->status = 4;
                $this->withdrawUpdate();
                $result['errno'] = 0;
            } else {
                \Log::debug($this->withdrawModel->pay_way . '提现错误' . $this->withdrawModel->withdraw_sn, $result);
            }
        }

        if ($this->withdrawModel->pay_way == 'wechat') {
            $memberModel = Member::uniacid()->where('uid', $this->withdrawModel->member_id)
                ->with(['hasOneFans', 'hasOneMiniApp', 'hasOneWechat'])->first();

            $v3_switch = false;
            if ($memberModel->hasOneFans->openid) {
                $income_set = Setting::get('shop.pay');
                $v3_switch = (bool)$income_set['weixin_apiv3'];
            } elseif (app('plugins')->isEnabled('min-app') && $memberModel->hasOneMiniApp->openid) {
                $appletSet = Setting::get('plugin.min_app');
                $v3_switch = (bool)$appletSet['v3_switch'];
            } elseif (app('plugins')->isEnabled('app-set') && $memberModel->hasOneWechat->openid) {
                $appSet = Setting::get('shop_app.pay');
                $v3_switch = (bool)$appSet['weixin_v3'];
            }
            if ($v3_switch && !empty($result) && 0 == $result['errno']) {
                //使用新版V3接口,保持打款中
                $this->withdrawModel->pay_at = time();
                $this->withdrawModel->status = 4;
                $this->withdrawUpdate();
            } else {
                \Log::debug(
                    $this->withdrawModel->pay_way . '提现' . $this->withdrawModel->withdraw_sn,
                    [$v3_switch, $result]
                );
            }
        }

        if ($this->withdrawModel->pay_way == 'converge_pay') {
            $this->withdrawModel->pay_at = time();
            $this->withdrawModel->status = 4;

            $this->withdrawUpdate();
        }

        return $result;
    }


    /**
     * 提现 model 数据保存
     * @return bool
     * @throws AppException
     */
    private function withdrawUpdate()
    {
        if (!$this->withdrawModel->save()) {
            throw new AppException('数据修改失败，请刷新重试');
        }
        return true;
    }


    /**
     * 提现打款，区分打款方式
     * @return mixed
     * @throws AppException
     */
    private function payment()
    {
        switch ($this->withdrawModel->pay_way) {
            case 'alipay':
                return $this->alipayPayment($this->paymentRemark());
            case 'wechat':
                return $this->wechatPayment();
            case 'manual':
                return $this->manualPayment();
            case 'eup_pay':
                return $this->eupPayment();
            case 'huanxun':
                return $this->huanxunPayment();
            case 'yop_pay': //易宝余额提现
                return $this->yopPayment();
            case 'converge_pay': //汇聚余额提现
                return $this->convergePayment($this->paymentRemark());
            case 'high_light_wechat': //高灯微信余额提现
            case 'high_light_alipay': //高灯支付宝余额提现
            case 'high_light_bank': //高灯银行卡余额提现
                return $this->highLightPayment();
            case 'worker_withdraw_wechat':
            case 'worker_withdraw_alipay':
            case 'worker_withdraw_bank':
                return $this->workWithdrawPayment();
            case 'eplus_withdraw_bank':
                return $this->eplusWithdrawPayment();
            case 'balance': //余额提现 todo:给保证金提现用
                return $this->balancePayment($this->paymentRemark());
            case 'silver_point':
                return $this->silverPointWithdrawPayment();
            case 'jianzhimao_bank':
                return $this->jianzhimaoBankPayment();
            case 'tax_withdraw_bank':
                return $this->taxWithdrawBankPayment();
            default:
                throw new AppException('未知打款方式！！！');
        }
    }


    /**
     * 打款日志（备注）
     * @return string
     */
    private function paymentRemark()
    {
        return $remark = '提现打款-' . $this->withdrawModel->type_name . '-金额:' . $this->withdrawModel->actual_amounts . '元,' . '手续费:' . $this->withdrawModel->actual_poundage;
    }

    /**
     * 支付宝打款
     * @param $remark
     * @param null $withdraw
     */
    private function alipayPayment($remark, $withdraw = null)
    {
        $result = [];

        if (!is_null($withdraw)) {
            $result = WithdrawService::alipayWithdrawPay($withdraw, $remark);
        } else {
            $result = WithdrawService::alipayWithdrawPay($this->withdrawModel, $remark);
        }

        Log::info('MemberId:' . $this->withdrawModel->member_id . ', ' . $remark . "支付宝打款中!");
        if (!empty($result) && 1 == $result['errno']) {
            return $this->paymentError($result['message']);
        }
        return $result;
    }


    /**
     * 微信打款
     * @return mixed
     */
    private function wechatPayment()
    {
        $result = WithdrawService::wechatWithdrawPay($this->withdrawModel, $this->paymentRemark());
        //file_put_contents(storage_path('logs/withdraw1.log'),print_r($resultPay,true));
        Log::info('MemberId:' . $this->withdrawModel->member_id . ', ' . $this->paymentRemark() . "微信打款中!");

        if (!empty($result) && 1 == $result['errno']) {
            return $this->paymentError($result['message']);
        }

        return $result;
        //return $this->paymentSuccess();
    }

    /**
     * 余额打款
     * @param $remark
     */
    private function balancePayment($remark)
    {
        $result = WithdrawService::balanceWithdrawPay($this->withdrawModel, $remark);

        Log::info('MemberId:' . $this->withdrawModel->member_id . ', ' . $remark . "余额打款中!");
        if (!empty($result) && 1 == $result['errno']) {
            return $this->paymentError($result['message']);
        }
        return $result;
    }

    /**
     * @return array|mixed|void
     * @author blank
     * EUP打款
     */
    private function eupPayment()
    {
        $result = WithdrawService::eupWithdrawPay($this->withdrawModel);

        if (!empty($result) && $result['errno'] == 1) {
            return $this->paymentError($result['message']);
        }

        return $result;
    }

    /**
     * @return array|mixed|void
     * @author blank
     * 易宝余额提现
     */
    private function yopPayment()
    {
        $result = WithdrawService::yopWithdrawPay($this->withdrawModel);

        if (!empty($result) && $result['errno'] == 1) {
            return $this->paymentError($result['message']);
        }

        return $result;
    }


    private function huanxunPayment()
    {
        $result = WithdrawService::huanxunPayment($this->withdrawModel);

        if ($result['result'] == 10 || $result['result'] == 8) {
            return ['errno' => 0, 'message' => '打款成功'];
        }
        $result['errno'] = 1;

        return $result;
    }

    /**
     * 汇聚余额提现
     *
     * @param $remark
     * @return array|mixed
     * @throws AppException
     */
    private function convergePayment($remark)
    {
        $result = WithdrawService::convergePayMent($this->withdrawModel, $remark);

        if ($result['verify']) {
            return $result;
        }

        $msg = "收入提现ID：{$this->withdrawModel->id}，汇聚提现失败：{$result['msg']}";
        return $this->paymentError($msg);
    }

    private function highLightPayment()
    {
        $result = WithdrawService::highLightWithdrawPay($this->withdrawModel);

//        if (!empty($result) && $result['errno'] == 500) {
//            return $this->paymentError($result['message']);
//        }
        return $result;
    }

    private function eplusWithdrawPayment()
    {
        return WithdrawService::eplusWithdrawPay($this->withdrawModel);
    }

    private function silverPointWithdrawPayment()
    {
        return WithdrawService::silverPointWithdrawPayment($this->withdrawModel);
    }

    private function jianzhimaoBankPayment()
    {
        return WithdrawService::jianzhimaoBankPayment($this->withdrawModel);
    }

    private function taxWithdrawBankPayment()
    {
        return WithdrawService::taxWithdrawBankPayment($this->withdrawModel);
    }

    private function workWithdrawPayment()
    {
        return WithdrawService::workerWithdrawPay($this->withdrawModel);
    }

    /**
     * 手动打款
     * @return mixed
     */
    private function manualPayment()
    {
        return ['errno' => 0, 'message' => '手动打款成功'];
        //return $this->paymentSuccess();
    }


    /**
     * 打款成功
     * @return mixed
     */
    private function paymentSuccess()
    {
        return $this->successJson('打款成功', yzWebUrl("finance.balance-withdraw.detail", ['id' => $this->getPostId()]));
    }


    /**
     * 打款失败
     * @param string $message
     * @throws AppException
     */
    private function paymentError($message = '')
    {
        $this->withdrawModel->status = 1;
        $this->withdrawUpdate();
        //发送打款失败通知 
        BalanceNoticeService::withdrawFailureNotice($this->withdrawModel);

        throw new AppException($message ?: '打款失败，请重试');
    }


    /**
     * 附值打款 model
     * @return mixed
     * @throws AppException
     */
    private function attachedMode()
    {
        $result = Withdraw::getBalanceWithdrawById($this->getPostId());

        if (!$result) {
            throw new AppException('数据错误，请刷新重试');
        }
        return $result;
    }


    /**
     * @return string
     */
    private function getPostId()
    {
        return trim(\YunShop::request()->id);
    }


    /**
     * @return string
     */
    private function getPostStatus()
    {
        return trim(\YunShop::request()->status);
    }


    /**
     * 丁冉增加批量打款
     * @return mixed
     */
    public function batchAlipay()
    {
        $ids = \YunShop::request()->ids;

        $withdrawId = explode(',', $ids);

        $withdraw = [];
        if (!empty($withdrawId)) {
            foreach ($withdrawId as $id) {
                $withdraw_modle = Withdraw::getBalanceWithdrawById($id);

                if (!is_null($withdraw_modle)) {
                    if ($withdraw_modle->status != '1') {
                        BalanceNoticeService::withdrawFailureNotice($withdraw_modle);

                        return $this->errorJson('打款失败,数据不存在或不符合打款规则!', yzWebUrl("finance.balance-withdraw.detail", ['id' => $id]));
                    }

                    $withdraw[] = $withdraw_modle;

                    $remark[] = '提现打款-' . $withdraw_modle->type_name . '-金额:' . $withdraw_modle->actual_amounts . '元,' .
                        '手续费:' . $withdraw_modle->actual_poundage;
                }
            }

            $remark = json_encode($remark);

            $this->alipayPayment($remark, $withdraw);
        }
    }
}
