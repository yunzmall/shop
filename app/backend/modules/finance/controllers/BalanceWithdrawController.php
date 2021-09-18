<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/14
 * Time: 下午5:06
 */

namespace app\backend\modules\finance\controllers;


use app\common\events\withdraw\BalanceWithdrawSuccessEvent;
use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\helpers\Url;
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
        $this->withdrawModel = $this->attachedMode();
        $set = Setting::getByGroup('pay_password') ?: [];
        return view('finance.balance.withdraw', [
            'item' => $this->withdrawModel->toArray(),
            'is_verify' => !empty($set['withdraw_verify']['is_phone_verify'])?true:false,
            'expire_time' => Session::get('withdraw_verify')?:null,
            'verify_phone' => $set['withdraw_verify']['phone']?:"",
            'verify_expire' => $set['withdraw_verify']['verify_expire']?intval($set['withdraw_verify']['verify_expire']):10
        ])->render();
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
        } elseif (isset($requestData['submit_cancel'])) {
            //重新审核
            return $this->submitCancel();
        } elseif (isset($requestData['submit_pay'])) {
            //打款
            return $this->submitPayNew();
        }elseif (isset($requestData['again_pay'])) {
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

            return $this->message('打款成功', yzWebUrl('finance.balance-withdraw.detail', ['id' => \YunShop::request()->id]));
        }
        return $this->message('提交数据有误，请刷新重试', yzWebUrl("finance.balance-withdraw.detail", ['id' => $this->getPostId()]), 'error');
    }

    /**
     * 把打款方法剔出，临时使用
     */
    private function submitPayNew()
    {
        $check = $this->checkVerify();//打款验证
        if (!$check) {
            $this->message('提现验证失败或验证已过期', yzWebUrl('finance.balance-withdraw.detail', ['id' => \YunShop::request()->id]), 'error');
        }

        $result = $this->submitPay();

        if (!empty($result) && 0 == $result['errno']) {
            //todo 临时增加手动打款成功通知，重构时候注意优化
            if ($this->withdrawModel->pay_way == 'manual') {
                event(new BalanceWithdrawSuccessEvent($this->withdrawModel));
                BalanceNoticeService::withdrawSuccessNotice($this->withdrawModel);
            }
            return $this->message('提现申请成功', yzWebUrl('finance.balance-withdraw.detail', ['id' => \YunShop::request()->id]));
        }
        BalanceNoticeService::withdrawFailureNotice($this->withdrawModel);//提现失败通知

        return $this->message('提现申请失败', yzWebUrl('finance.balance-withdraw.detail', ['id' => \YunShop::request()->id]), 'error');
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

    /**
     * 提现审核
     * @return mixed
     */
    private function submitCheck()
    {

        $this->withdrawModel->status = $this->getPostStatus();
        $this->withdrawModel->audit_at = time();

        if ($this->getPostStatus() == -1) {
            BalanceNoticeService::withdrawFailureNotice($this->withdrawModel);
        }
        if ($this->getPostStatus() == 3) {
            return (new AuditRejectedController())->index();
            BalanceNoticeService::withdrawRejectNotice($this->withdrawModel);
        }
        $this->withdrawUpdate();
        return $this->message('提交审核成功', yzWebUrl("finance.balance-withdraw.detail", ['id' => $this->getPostId()]));
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
        } elseif ($this->withdrawModel->pay_way == 'alipay' || $this->withdrawModel->pay_way == 'yop_pay') {
            $this->withdrawModel->pay_at = time();
            $this->withdrawModel->status = 4;

            $this->withdrawUpdate();
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
                break;
            case 'wechat':
                return $this->wechatPayment();
                break;
            case 'manual':
                return $this->manualPayment();
                break;
            case 'eup_pay':
                return $this->eupPayment();
                break;
            case 'huanxun':
                return $this->huanxunPayment();
                break;
            case 'yop_pay': //易宝余额提现
                return $this->yopPayment();
                break;
            case 'converge_pay': //汇聚余额提现
                return $this->convergePayment($this->paymentRemark());
                break;
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
        return $this->message('打款成功', yzWebUrl("finance.balance-withdraw.detail", ['id' => $this->getPostId()]));
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

                        return $this->message('打款失败,数据不存在或不符合打款规则!', yzWebUrl("finance.balance-withdraw.detail", ['id' => $id]), 'error');
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
