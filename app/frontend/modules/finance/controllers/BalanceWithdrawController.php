<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/28 下午3:49
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:
 ****************************************************************/

namespace app\frontend\modules\finance\controllers;


use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\models\McMappingFans;
use app\common\services\credit\ConstService;
use app\common\services\finance\BalanceChange;
use app\common\services\finance\BalanceNoticeService;
use app\common\services\password\PasswordService;
use app\frontend\models\Member;
use app\frontend\models\MemberShopInfo;
use app\frontend\modules\finance\models\Withdraw;
use app\frontend\modules\finance\models\WithdrawSetLog;
use app\frontend\modules\finance\services\WithdrawManualService;
use app\frontend\modules\member\models\MemberBankCard;
use app\frontend\modules\withdraw\services\WithdrawMessageService;
use Illuminate\Support\Facades\DB;
use app\common\events\withdraw\WithdrawBalanceAppliedEvent;
use app\common\helpers\Url;
use app\frontend\modules\withdraw\services\StatisticalPresentationService;

class BalanceWithdrawController extends BalanceController
{

    public $withdrawModel;

    /**
     * @var Member
     */
    public $memberModel;


    public function preAction()
    {
        parent::preAction();
        $this->memberModel = $this->getMemberModel();
    }

    /**
     * 余额提现页面按钮接口
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function page()
    {
        $data = [
            'balance'                => $this->memberModel->credit2 ?: 0,
            'wechat'                 => $this->balanceSet->withdrawWechat(),
            'alipay'                 => $this->balanceSet->withdrawAlipay(),
            'manual'                 => $this->balanceSet->withdrawManual(),
            'huanxun'                => $this->balanceSet->withdrawHuanxun(),
            'eup_pay'                => $this->balanceSet->withdrawEup(),
            'converge_pay'           => $this->balanceSet->withdrawConverge(),
            'withdraw_multiple'      => $this->balanceSet->withdrawMultiple(),
            'high_light_wechat'      => $this->balanceSet->withdrawHighLight('high_light_wechat'),
            'high_light_alipay'      => $this->balanceSet->withdrawHighLight('high_light_alipay'),
            'high_light_bank'        => $this->balanceSet->withdrawHighLight('high_light_bank'),
            'worker_withdraw_wechat' => $this->balanceSet->workerWithdrawEnable('worker_withdraw_wechat'),
            'worker_withdraw_alipay' => $this->balanceSet->workerWithdrawEnable('worker_withdraw_alipay'),
            'worker_withdraw_bank'   => $this->balanceSet->workerWithdrawEnable('worker_withdraw_bank'),
            'eplus_withdraw_bank'    => $this->balanceSet->eplusWithdrawEnable(),
            'silver_point'           => $this->balanceSet->silverPointWithdrawEnable(),
            'support_center_withdraw'=> $this->balanceSet->supportCenterWithdrawEnable(),
            'support_center_withdraw_name'=> $this->balanceSet->supportCenterWithdrawName(),
            'bank_card_status'       => $this->memberBankCardStatus(),
            'extra_data'             => $this->balanceSet->extraData(),
            'poundage'               => $this->getPagePoundage(),
            'has_password'           => $this->hasPassword(),
            'need_password'          => $this->needWithdrawPassword(),
            'jianzhimao_bank'        => $this->balanceSet->jianzhimaoBankWithdrawEnable(),
            'tax_withdraw_bank'      => $this->balanceSet->taxWithdrawBankEnable(),
            'withdraw_diy_name'      => $this->diyName(),
            'withdraw_extra_data'    => $this->getExtraData(),
        ];

        return $this->successJson('获取数据成功', $data);
    }

    private function hasPassword()
    {
        return $this->memberModel->yzMember->hasPayPassword();
    }

    private function needWithdrawPassword()
    {
        return (new PasswordService())->isNeed('balance', 'withdraw');
    }

    private function memberBankCardStatus(): bool
    {
        return WithdrawManualService::getBankStatus();
    }

    public function withdraw()
    {
        if ($this->needWithdrawPassword()) {
            (new PasswordService())->checkPayPassword($this->memberId(), $this->password());
        }

        if (!$this->balanceSet->withdrawSet()) {
            return $this->errorJson('未开启余额提现');
        }

        return $this->withdrawStart();
    }


    public function isCanSubmit()
    {
        if ($this->balanceSet->withdrawManual()) {
            return $this->successJson('ok', $this->manualIsCanSubmit());
        }
        return $this->errorJson('未开启余额手动提现');
    }


    private function getPagePoundage()
    {
        $lang = \Setting::get('shop.lang.lang');

        $langData = \Setting::get('shop.lang.' . $lang);

        $poundageName = $langData['income']['poundage_name'] ?: '手续费';


        $withdraw_poundage = $this->balanceSet->withdrawPoundage();
        if (empty($withdraw_poundage)) {
            return '';
        }
        $poundage = $poundageName . '比例：' . $this->balanceSet->withdrawPoundage() . '%';
        if ($this->balanceSet->withdrawPoundageType() == 1) {
            $poundage = "固定{$poundageName}：" . $this->balanceSet->withdrawPoundage() . '元';
        }

        $poundage_full_cut = $this->balanceSet->withdrawPoundageFullCut();
        if (!empty($poundage_full_cut)) {
            $poundage = $poundage . "，提现金额满" . $poundage_full_cut . "元减免{$poundageName}";
        }
        return $poundage;
    }


    private function withdrawStart()
    {
        $withdrawType = $this->getWithdrawType();
        if ($withdrawType == 'wechat' && !$this->balanceSet->withdrawWechat()) {
            return $this->errorJson('未开启提现到微信');
        }
        if ($withdrawType == 'alipay' && !$this->balanceSet->withdrawAlipay()) {
            return $this->errorJson('未开启提现到支付宝');
        }
        if ($withdrawType == 'alipay' && !$this->getMemberAlipaySet()) {
            return $this->errorJson('您未配置支付宝信息，请先修改个人信息中支付宝信息', ['status' => 1]);
        }
        if ($withdrawType == 'manual' && !$this->balanceSet->withdrawManual()) {
            return $this->errorJson('未开启余额手动提现');
        }
        if ($withdrawType == 'eup_pay' && !$this->balanceSet->withdrawEup()) {
            return $this->errorJson('未开启余额EUP提现');
        }
        if ($withdrawType == 'converge_pay' && !$this->balanceSet->withdrawConverge()) {
            return $this->errorJson('未开启余额汇聚提现');
        }
        if ($withdrawType == 'silver_point' && !$this->balanceSet->silverPointWithdrawEnable()) {
            return $this->errorJson('未开启余额银典支付提现');
        }

        if ($withdrawType == 'jianzhimao_bank' && !$this->balanceSet->jianzhimaoBankWithdrawEnable()) {
            return $this->errorJson('未开启兼职猫-银行卡提现');
        }

        if ($withdrawType == 'tax_withdraw_bank' && !$this->balanceSet->taxWithdrawBankEnable()) {
            $diy_name = '税惠添薪';
            if (app('plugins')->isEnabled('tax-withdraw')) {
                $diy_name = TAX_WITHDRAW_DIY_NAME;
            }

            return $this->errorJson('未开启' . $diy_name . '-银行卡提现');
        }

        if (in_array($withdrawType, ['high_light_wechat', 'high_light_alipay', 'high_light_bank']
            ) && !$this->balanceSet->withdrawHighLight($withdrawType)) {
            return $this->errorJson('未开启余额高灯提现');
        }
        $manual_result = $this->manualIsCanSubmit();
        if ($withdrawType == 'manual' && !$manual_result['status']) {
            return $this->errorJson('需要完善信息', $manual_result);
        }

        if (in_array($withdrawType, ['high_light_wechat', 'high_light_alipay', 'high_light_bank'])) {
            $this->highLightIsCanSubmit($withdrawType);
        }

        if (in_array($withdrawType, ['worker_withdraw_wechat', 'worker_withdraw_alipay', 'worker_withdraw_bank'])) {
            $this->workerWithdrawCanSubmit($withdrawType);
        }

        if (in_array($withdrawType, ['eplus_withdraw_bank'])) {
            $this->eplusWithdrawCanSubmit();
        }

        if ($withdrawType == 'support_center_withdraw' && !$this->balanceSet->supportCenterWithdrawEnable()) {
            return $this->errorJson('未开启' . $this->balanceSet->supportCenterWithdrawName());
        }

        $withdrawFetter = $this->balanceSet->withdrawAstrict();
        if ($withdrawFetter > $withdrawMoney = $this->getWithdrawMoney()) {
            return $this->errorJson('提现金额不能小于' . $withdrawFetter . '元');
        }
        $multiple = $this->balanceSet->withdrawMultiple();
        if (!empty($multiple) && fmod($withdrawMoney, $multiple) != 0) {
            throw new AppException('提现值必须是' . $multiple . '的倍数');
        }

        if (bcsub($this->getWithdrawMoney(), $this->getPoundage(), 2) < 1) {
            return $this->errorJson('扣除手续费后的金额不能小于1元');
        }

        $this->cashLimitation();

        DB::beginTransaction();

        //写入提现记录
        $this->withdrawModel = new Withdraw();

        $this->withdrawModel->fill($this->getWithdrawData());
        $validator = $this->withdrawModel->validator();
        if ($validator->fails()) {
            return $this->errorJson($validator->messages()->first());
        }
        if (!$this->withdrawModel->save()) {
            return $this->errorJson('提现失败，记录写入失败');
        }


        //写入提现关联表，当前设置记录
        $withdrawSetLog = new WithdrawSetLog();

        $withdrawSetLog->fill($this->getWithdrawSetLogData());
        $validator = $withdrawSetLog->validator();
        if ($validator->fails()) {
            return $this->errorJson($validator->messages()->first());
        }
        if (!$withdrawSetLog->save()) {
            return $this->errorJson('提现失败，记录Log写入失败');
        }


        //修改会员余额
        $result = (new BalanceChange())->withdrawal($this->getBalanceChangeData());
        if ($result === true) {
            DB::commit();
            app('plugins')->isEnabled('converge_pay') && Setting::get(
                'withdraw.balance.audit_free'
            ) == 1 && $withdrawType == 'converge_pay' ? \Setting::set(
                'plugin.convergePay_set.notifyWithdrawUrl',
                Url::shopSchemeUrl('payment/convergepay/notifyUrlWithdraw.php')
            ) : null;
            event(new WithdrawBalanceAppliedEvent($this->withdrawModel));
            BalanceNoticeService::withdrawSubmitNotice($this->withdrawModel);
            //提现通知管理员
            (new WithdrawMessageService())->withdraw($this->withdrawModel);

            if (app('plugins')->isEnabled('shop-assistant')) {
                \Log::info('余额提现-店铺助手申请');
                (new \Yunshop\ShopAssistant\services\MessageService)->withdrawAudit($this->withdrawModel);
            }
            return $this->successJson('提现申请成功');
        }


        DB::rollBack();
        return $this->errorJson('提现写入失败，请联系管理员');
    }

    //提现限制
    private function cashLimitation()
    {
        $set = Setting::get('withdraw.balance');

        $withdrawType = $this->getWithdrawType();
        $withdrawAmount = $this->getWithdrawMoney();

        //今日已经提现次数（收入提现 + 供应商提现）
        $submittedCount = (new StatisticalPresentationService())->statisticalPresentation($withdrawType);

        //统计用户今天提现的次数
        $count = $submittedCount + 1;

        switch ($withdrawType) {
            case Withdraw::TAX_WITHDRAW_BANK:
                $min = $set['tax_withdraw_bank_min'];
                $max = $set['tax_withdraw_bank_max'];
                $limitCount = floor($set['tax_withdraw_bank_frequency'] ?: 10);
                break;
            case Withdraw::WITHDRAW_WITH_JIANZHIMAO_BANK:
                $min = $set['jianzhimao_bank_min'];
                $max = $set['jianzhimao_bank_max'];
                $limitCount = floor($set['jianzhimao_bank_frequency'] ?: 10);
                break;
            case Withdraw::WITHDRAW_WITH_SILVER_POINT:
                $min = $set['silver_point_min'];
                $max = $set['silver_point_max'];
                $limitCount = floor($set['silver_point_frequency'] ?: 10);
                break;
            case Withdraw::WITHDRAW_WITH_WECHAT:
                $min = $set['wechat_min'];
                $max = $set['wechat_max'];
                $limitCount = floor($set['wechat_frequency'] ?: 10);
                break;
            case Withdraw::WITHDRAW_WITH_ALIPAY:
                $min = $set['alipay_min'];
                $max = $set['alipay_max'];
                $limitCount = floor($set['alipay_frequency'] ?: 10);
                break;
            case Withdraw::WITHDRAW_WITH_MANUAL:
                $min = $set['manual_min'];
                $max = $set['manual_max'];
                $limitCount = floor($set['manual_frequency'] ?: 0);
                break;
            case Withdraw::WITHDRAW_WITH_EPLUS_WITHDRAW_BANK:
                $min = $set['eplus_withdraw_bank_min'];
                $max = $set['eplus_withdraw_bank_max'];
                $limitCount = floor($set['eplus_withdraw_bank_frequency'] ?: 0);
            case Withdraw::WITHDRAW_WITH_WORK_WITHDRAW_WECHAT:
                $min = $set['worker_withdraw_wechat_min'];
                $max = $set['worker_withdraw_wechat_max'];
                $limitCount = floor($set['worker_withdraw_wechat_frequency'] ?: 0);
                break;
            case Withdraw::WITHDRAW_WITH_WORK_WITHDRAW_BANK:
                $min = $set['worker_withdraw_bank_min'];
                $max = $set['worker_withdraw_bank_max'];
                $limitCount = floor($set['worker_withdraw_bank_frequency'] ?: 0);
                break;
            case Withdraw::WITHDRAW_WITH_WORK_WITHDRAW_ALIPAY:
                $min = $set['worker_withdraw_alipay_min'];
                $max = $set['worker_withdraw_alipay_max'];
                $limitCount = floor($set['worker_withdraw_alipay_frequency'] ?: 0);
                break;
            case Withdraw::WITHDRAW_WITH_HIGH_LIGHT_WECHAT:
            case Withdraw::WITHDRAW_WITH_HIGH_LIGHT_ALIPAY:
            case Withdraw::WITHDRAW_WITH_HIGH_LIGHT_BANK:
                if ($withdrawAmount < 1) {
                    return $this->errorJson("余额" . Withdraw::getPayWayComment($withdrawType) . "失败，提现额必须大于等于1元");
                } elseif ($withdrawType == Withdraw::WITHDRAW_WITH_HIGH_LIGHT_WECHAT && $withdrawAmount > 100000) {
                    return $this->errorJson("高灯微信单笔提现不得大于10万元");
                } elseif ($withdrawType == Withdraw::WITHDRAW_WITH_HIGH_LIGHT_ALIPAY && $withdrawAmount > 400000) {
                    return $this->errorJson("高灯支付宝单笔提现不得大于40万元！");
                } elseif ($withdrawType == Withdraw::WITHDRAW_WITH_HIGH_LIGHT_BANK && $withdrawAmount > 100000) {
                    return $this->errorJson("高灯银行卡单笔提现不得大于10万元！");
                }
                break;
            case Withdraw::WITHDRAW_WITH_CONVERGE_PAY:
                $min = $set['converge_pay_min'];
                $max = $set['converge_pay_max'];
                $limitCount = floor($set['converge_pay_frequency'] ?: 0);
                break;
            default:
                $min = 0;
                $max = 0;
                $limitCount = 0;
        }

        $payWayName = Withdraw::getPayWayComment($withdrawType);

        if ($min && $withdrawAmount < $min) {
            \Log::debug("余额{$payWayName}失败", ['提现金额', $withdrawAmount, '提现最小额', $min]);

            return $this->errorJson("余额{$payWayName}失败,{$payWayName}最小额度{$min}元");
        }
        if ($max && $withdrawAmount > $max) {
            \Log::debug("余额{$payWayName}失败", ['提现金额', $withdrawAmount, '提现最大额', $max]);

            return $this->errorJson("余额{$payWayName}失败,{$payWayName}最大额度{$max}元");
        }
        if ($limitCount && $count > $limitCount) {
            \Log::debug("余额{$payWayName}失败", ['今天提现次数', $submittedCount, '每日限制次数', $limitCount]);

            return $this->errorJson("余额{$payWayName}失败,每日{$payWayName}次数不能超过{$limitCount}次");
        }
    }

    /**
     * @return array
     */
    private function getWithdrawData()
    {
        $dalance = Setting::get('shop.shop');
        $dalance['credit'] = empty($dalance['credit']) ? "余额" : $dalance['credit'];
        return array(
            'withdraw_sn'     => Withdraw::createOrderSn('WS', 'withdraw_sn'),
            'uniacid'         => $this->uniacid,
            'member_id'       => $this->memberModel->uid,
            'type'            => 'balance',
            'type_id'         => '',
            'type_name'       => $dalance['credit'],//'余额提现'
            'amounts'         => $this->getWithdrawMoney(),                   //提现金额
            'poundage'        => $this->getPoundage(),                        //提现手续费
            'poundage_rate'   => $this->balanceSet->withdrawPoundageType() ? '0' : $this->PoundageRate(),//手续费比例
            'pay_way'         => $this->getWithdrawType(),                    //打款方式
            'status'          => '0',                                         //0未审核，1未打款，2已打款， -1无效
            'actual_amounts'  => bcsub($this->getWithdrawMoney(), $this->getPoundage(), 2),
            'actual_poundage' => $this->getPoundage(),
            'manual_type'     => Setting::get('withdraw.balance')['balance_manual_type'] ?: 1,
        );
    }


    /**
     * @return array
     */
    private function getWithdrawSetLogData()
    {
        return [
            'withdraw_id'       => $this->withdrawModel->id,
            'poundage_type'     => $this->balanceSet->withdrawPoundageType(),
            'poundage'          => $this->balanceSet->withdrawPoundage(),
            'poundage_full_cut' => $this->balanceSet->withdrawPoundageFullCut(),
            'withdraw_fetter'   => $this->balanceSet->withdrawAstrict(),
            'remark'            => '',
            'created_at'        => time()
        ];
    }


    /**
     * 获取余额提现改变余额 data 数据
     * @return array
     */
    private function getBalanceChangeData()
    {
        return array(
            'member_id'    => \YunShop::app()->getMemberId(),
            'remark'       => '会员余额提现' . $this->withdrawModel->amounts,
            'source'       => ConstService::SOURCE_WITHDRAWAL,
            'relation'     => $this->withdrawModel->withdraw_sn,
            'operator'     => ConstService::OPERATOR_MEMBER,
            'operator_id'  => $this->withdrawModel->member_id,
            'change_value' => $this->withdrawModel->amounts
        );
    }


    /**
     * 当前提现金额需支付手续费值
     * @return string
     */
    private function getPoundage()
    {
        if (!$this->isHasPoundage()) {
            return '0';
        } elseif ($this->balanceSet->withdrawPoundageType() == 1) {
            return $this->balanceSet->withdrawPoundage();
        } else {
            return bcdiv(bcmul($this->getWithdrawMoney(), $this->balanceSet->withdrawPoundage(), 4), 100, 2);
        }
    }


    /**
     * 手续费比例设置值，可以是固定金额，也可以是比例，需要通过 poundage_type 判断
     * @return string
     */
    private function PoundageRate()
    {
        return $this->isHasPoundage() ? $this->balanceSet->withdrawPoundage() : '0';
    }


    /**
     * 增加 提现金额 满N元 减免手续费，true 正常计算手续费，false 减免手续费 YITIAN::2017-09-28
     * @return bool
     */
    private function isHasPoundage()
    {
        $poundage_full_cut = $this->balanceSet->withdrawPoundageFullCut();
        if (!empty($poundage_full_cut)) {
            return bccomp($this->getWithdrawMoney(), $poundage_full_cut, 2) != -1 ? false : true;
        }
        return true;
    }


    private function getWithdrawMoney()
    {
        $withdraw_money = trim(\YunShop::request()->withdraw_money);
        if ($withdraw_money) {
            return $withdraw_money;
        }
        throw new AppException('未获取到提现金额');
    }


    private function getWithdrawType()
    {
        $requestType = request()->input('withdraw_type');

        $result = $this->withdrawTypeArray()[$requestType] ?? '';

        if (!$result) {
            throw new AppException('未找到提现类型');
        }
        return $result;
    }

    private function withdrawTypeArray(): array
    {
        return [
            1  => 'wechat',
            2  => 'alipay',
            3  => 'manual',
            4  => 'eup_pay',
            5  => 'huanxun',
            6  => 'converge_pay',
            7  => 'high_light_wechat',
            8  => 'high_light_alipay',
            9  => 'high_light_bank',
            10 => 'worker_withdraw_wechat',
            11 => 'worker_withdraw_alipay',
            12 => 'worker_withdraw_bank',
            13 => 'eplus_withdraw_bank',
            14 => 'silver_point',
            15 => 'support_center_withdraw',
            16 => 'jianzhimao_bank',
            17 => 'tax_withdraw_bank'
        ];
    }


    private function manualIsCanSubmit()
    {
        $manual_type = Setting::get('withdraw.balance')['balance_manual_type'] ?: 1;

        switch ($manual_type) {
            case 2:
                $result['manual_type'] = 'wechat';
                $result['status'] = WithdrawManualService::getWeChatStatus();
                break;
            case 3:
                $result['manual_type'] = 'alipay';
                $result['status'] = WithdrawManualService::getAlipayStatus();
                break;
            default:
                $result['manual_type'] = 'bank';
                $result['status'] = WithdrawManualService::getBankStatus();
        }
        return $result;
    }

    private function eplusWithdrawCanSubmit()
    {
        if (!app('plugins')->isEnabled('eplus-pay')) {
            throw new AppException('智E+插件未开启');
        }
        if (!\Yunshop\EplusPay\services\SettingService::usable()) {
            throw new AppException('智E+插件未启用');
        }
        if (!\Yunshop\EplusPay\services\SettingService::isNameAuth()) {
            throw new AppException('请先完成账户认证');
        }
        if (!request()->bank_card_no) {
            throw new AppException('请选择要提现的银行卡');
        }
    }

    private function workerWithdrawCanSubmit($withdrawType)
    {
        if (!app('plugins')->isEnabled('worker-withdraw')) {
            throw new AppException('好灵工插件未开启');
        }
        $res = \Yunshop\WorkerWithdraw\services\SettingService::getRequestAccountByMember(
            \YunShop::app()->getMemberId(),
            $withdrawType
        );
        if (!$res['code']) {
            throw new AppException($res['message']);
        }
    }

    private function highLightIsCanSubmit($withdrawType)
    {
        try {
            $agreementInfo = \Yunshop\HighLight\services\AgreementService::agreementInfo(
                ['member_id' => \Yunshop::app()->getMemberId()]
            )->first();
            if (!$agreementInfo || !\Yunshop\HighLight\services\AgreementService::checkAgreement($agreementInfo)) {
                $is_check = false;
            } else {
                $is_check = true;
            }
        } catch (\Exception $e) {
            throw new AppException($e->getMessage());
        }
        if (!$is_check) {
            throw new AppException('您未完成高灯签约，暂不能进行提现', ['high_light' => 1]);
        }
        if(!\Yunshop\HighLight\services\ApiService::current()->getSupplierNum()){
            throw new AppException('没选择项目所在地，暂不能进行提现');
        }
        if ($agreementInfo->certificate_type == 1) {
            $year = substr($agreementInfo->certificate_no, 6, 4);
            if ((date('Y') - $year) > 65) {
                throw new AppException('超龄警告，大于65岁的会员无法进行此方式提现', ['high_light' => 1]);
            }
        }
        switch ($withdrawType) {
            case 'high_light_wechat':
                $fans = McMappingFans::where('uid', \Yunshop::app()->getMemberId())->first();
                if (!$fans) {
                    throw new AppException('您未在公众号商城中授权登录过，无法进行高灯微信提现');
                }
                break;
            case 'high_light_alipay':
                if (!$agreementInfo->payment_account) {
                    throw new AppException('请您填写好所要提现到的支付宝账号', ['high_light' => 1]);
                }
                break;
            case 'high_light_bank':
                if (!$agreementInfo->bank_name || !$agreementInfo->bankcard_num) {
                    throw new AppException('请您填写好所要提现到的银行信息', ['high_light' => 1]);
                }
                break;
            default:
                throw new AppException('未知提现类型');
        }
        return true;
    }


//***********************************  以下方法可以在member model 中实现  ***********************************************//


    /**
     * 获取会员 支付宝 设置，
     * @return bool
     */
    private function getMemberAlipaySet()
    {
        $array = MemberShopInfo::select('alipay', 'alipayname')->where(
            'member_id',
            \YunShop::app()->getMemberId()
        )->first();
        if ($array && $array['alipay'] && $array['alipayname']) {
            return true;
        }
        return false;
    }

    /**
     * 获取验证登录会员是否存在， 因支付宝不需要验证，暂时从 BalanceController 中提出来，
     * @return mixed
     * @throws AppException
     */
    private function getMemberModel()
    {
        $memberModel = Member::where('uid', \YunShop::app()->getMemberId())->first();
        if ($memberModel) {
            return $memberModel;
        }
        throw new AppException('未获取到会员信息');
    }

    public function convergeWithdraw()
    {
        $data['cost_money'] = number_format($this->getWithdrawMoney(), 2);
        $data['actual_amount'] = bcsub($this->getWithdrawMoney(), $this->getPoundage(), 2);
        $data['poundage'] = number_format($this->getPoundage(), 2);

        return $this->successJson('获取数据成功', $data);
    }

    /**
     * @description 展示提现自定义名称
     */
    private function diyName(): array
    {
        $array = [];

        if (app('plugins')->isEnabled('tax-withdraw')) {
            $array['tax_withdraw_bank'] = TAX_WITHDRAW_DIY_NAME;
        }

        return $array;
    }

    /**
     * @description 判断提现类型的额外判断
     * @return array
     */
    private function getExtraData(): array
    {
        $array = [];

        if (app('plugins')->isEnabled('tax-withdraw')) {
            $array['tax_withdraw_bank'] = \Yunshop\TaxWithdraw\services\TaxService::getExtraDataBank();
        }

        if (app('plugins')->isEnabled('jianzhimao-withdraw')) {
            $array['jianzhimao_bank'] = \Yunshop\JianzhimaoWithdraw\services\JianzhimaoService::getExtraDataBank();
        }

        return $array;
    }
}
