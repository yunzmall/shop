<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/5/31 下午2:40
 * Email: livsyitian@163.com
 */

namespace app\frontend\modules\withdraw\controllers;


use app\common\components\ApiController;
use app\common\events\withdraw\WithdrawAppliedEvent;
use app\common\events\withdraw\WithdrawApplyEvent;
use app\common\events\withdraw\WithdrawApplyingEvent;
use app\common\exceptions\AppException;
use app\common\facades\Setting;
use app\common\models\Income;
use app\common\models\income\WithdrawIncomeApply;
use app\common\services\income\WithdrawIncomeApplyService;
use app\frontend\modules\withdraw\models\Withdraw;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use app\frontend\modules\withdraw\services\StatisticalPresentationService;
use app\common\helpers\Url;
use Yunshop\Commission\models\Agents;
use app\common\models\WithdrawMergeServicetaxRate;
use Yunshop\WithdrawalLimit\Common\models\MemberWithdrawalLimit;
use app\common\services\finance\Withdraw as WithdrawService;

class ApplyController extends ApiController
{
    private $withdraw_set;

    /**
     * @var static
     */
    private $pay_way;

    /**
     * @var double
     */
    private $amount;

    /**
     * @var double
     */
    private $poundage;


    /**
     * @var array
     */
    private $withdraw_data;


    public function __construct()
    {
        parent::__construct();

        $this->withdraw_set = $this->getWithdrawSet();
    }

    //提现接口
    public function index()
    {
        $this->withdrawLimitation();
        list($amount, $pay_way, $poundage, $withdraw_data) = $this->getPostValue();
        $this->amount = $amount;
        $this->pay_way = $pay_way;
        $this->poundage = $poundage;
        $this->withdraw_data = $withdraw_data;
        //提现数据验证
        $this->validateData();
        //提现限额判断
        $this->cashLimitation();
       //提现额度插件判断
        $this->withdrawalLimitation();
        //插入提现
        $result = $this->withdrawStart();

        $set = \Setting::get('shop.lang.zh_cn.income');

        $name = '提现';
        if ($set['name_of_withdrawal']){
            $name = $set['name_of_withdrawal'];
        }
        if ($result === true) {
            return $this->successJson($name.'申请成功');
        }
        return $this->errorJson($result ?: $name.'申请失败');
    }

    private function cashLimitation(){
        $set = Setting::get('withdraw.income');

        //提交提现的次数
        $number_of_submissions = count($this->withdraw_data);

        if( $this->pay_way == 'wechat'){
            $wechat_frequency = floor($set['wechat_frequency'] ?: 10 );
            //统计用户今天提现的次数
            $statisticalPresentationService = new StatisticalPresentationService;
            $today_withdraw_count = $statisticalPresentationService->statisticalPresentation('wechat');

            if(($number_of_submissions + $today_withdraw_count) > $wechat_frequency  ){
                \Log::debug('提现到微信失败',['今天提现次数',$today_withdraw_count,'本次提现次数',$number_of_submissions,'每日限制次数',$wechat_frequency]);
                return $this->errorJson('提现失败,每日提现到微信次数不能超过'.$wechat_frequency.'次');
            }
        }elseif($this->pay_way == 'alipay'){
            $alipay_frequency = floor($set['alipay_frequency'] ?: 10);
            //统计用户今天提现的次数  + 供应商提现的次数
            $statisticalPresentationService = new StatisticalPresentationService;
            $today_withdraw_count = $statisticalPresentationService->statisticalPresentation('alipay');
            if(($number_of_submissions + $today_withdraw_count) > $alipay_frequency  ){
                \Log::debug('提现到支付宝失败',['今天提现次数',$today_withdraw_count,'本次提现次数',$number_of_submissions,'每日限制次数',$alipay_frequency]);
                return $this->errorJson('提现失败,每日提现到支付宝次数不能超过'.$alipay_frequency.'次');

            }
        }
    }

    private function withdrawalLimitation()
    {
        if(app('plugins')->isEnabled('withdrawal-limit'))
        {
            $set = \Setting::get('withdrawal-limit.is_open');
            if ($set != 1 || !in_array($this->pay_way,MemberWithdrawalLimit::$payWays)) {
                return;
            }
            $ways = json_decode(\Setting::get('withdrawal-limit.way'),true);
            $mark = false;
            switch ($this->pay_way)
            {
                case Withdraw::WITHDRAW_WITH_ALIPAY:
                    if($ways['alipay'] == 1){
                        $mark = true;
                    }
                    break;
                case Withdraw::WITHDRAW_WITH_WECHAT:
                    if($ways['wechat'] == 1){
                        $mark = true;
                    }
                    break;
                case Withdraw::WITHDRAW_WITH_MANUAL:
                    if($ways['bankcard'] == 1){
                        $mark = true;
                    }
                    break;
            }
            if($mark)
            {
                $memberModel = MemberWithdrawalLimit::uniacid()->where('member_id',\YunShop::app()->getMemberId())->first();
                if($memberModel){
                    $limit = $memberModel->total_amount;
                }else{
                    $limit = 0;
                }
                if($this->amount > $limit)
                {
                    return $this->errorJson('当前提现额度不足，暂不能提现');
                }
            }
        }
        if(app('plugins')->isEnabled('high-light') && in_array($this->pay_way,[
                Withdraw::WITHDRAW_WITH_HIGH_LIGHT_WECHAT,
                Withdraw::WITHDRAW_WITH_HIGH_LIGHT_ALIPAY,
                Withdraw::WITHDRAW_WITH_HIGH_LIGHT_BANK
        ])) {
            try {
                if ($this->amount < 1) {
                    throw new \Exception('高灯提现金额必须大于等于1元');
                }
                switch ($this->pay_way)
                {
                    case Withdraw::WITHDRAW_WITH_HIGH_LIGHT_WECHAT:
                        if ($this->amount > 100000) {
                            throw new \Exception('高灯微信单笔提现不得大于10万元！');
                        }
                        break;
                    case Withdraw::WITHDRAW_WITH_HIGH_LIGHT_ALIPAY:
                        if ($this->amount > 400000) {
                            throw new \Exception('高灯微信单笔提现不得大于40万元！');
                        }
                        break;
                    case Withdraw::WITHDRAW_WITH_HIGH_LIGHT_BANK:
                        if ($this->amount > 100000) {
                            throw new \Exception('高灯银行卡单笔提现不得大于10万元！');
                        }
                        break;
                }
            } catch (\Exception $e) {
                return $this->errorJson($e->getMessage());
            }
        }
    }

    private function withdrawLimitation()
    {
        if (app('plugins')->isEnabled('commission')) {
            $set = \Setting::get('plugin.commission');
            $agent = Agents::uniacid()->where('member_id', \YunShop::app()->getMemberId())->with('agentLevel')->first();
            if (!$agent->agent_level_id) {
                if ($set['no_withdraw']) {
                    return $this->errorJson('不满足分销商等级，不可提现',['status' => 0]);
                }
            } else {
                if ($agent->agentLevel->no_withdraw) {
                    return $this->errorJson('不满足分销商等级，不可提现',['status' => 0]);
                }
            }
        }
        $this->validateWithdrawDate();
    }

    private function withdrawStart()
    {
        try{

            DB::transaction(function () {
                $this->_withdrawStart();
            });
            return true;

        } catch (\Exception $exception) {
           throw $exception;
        }
    }


    /**
     * @return bool
     * @throws AppException|
     */
    private function _withdrawStart()
    {
        $amount = '0';

        if (count($this->withdraw_data) > 1){ // 如果同时提现几种类型的收入并且后台设置了劳务税金额梯度比例，劳务税按金额总和计算
            if ($this->withdraw_set['servicetax']){
                $merge_servicetax_withdraw_id = []; //劳务税id
                $merge_servicetax_amount = 0; //劳务税计算金额
            }
        }

        foreach ($this->withdraw_data as $key => $item) {

            $withdrawModel = new Withdraw();

            $withdrawModel->mark = $item['key_name'];
            $withdrawModel->withdraw_set = $this->withdraw_set;
            $withdrawModel->income_set = $this->getIncomeSet($item['key_name']);
            $withdrawModel->fill($this->getWithdrawData($item));

            event(new WithdrawApplyEvent($withdrawModel));
            
            $validator = $withdrawModel->validator();
            if ($validator->fails()) {
                throw new AppException("ERROR:Data anomaly -- {$item['key_name']}::{$validator->messages()->first()}");
            }

            event(new WithdrawApplyingEvent($withdrawModel));


            if (!$withdrawModel->save()) {
                throw new AppException("ERROR:Data storage exception -- {$item['key_name']}");
            }

			//判断收入是否已提现
			$apply_count = WithdrawIncomeApply::whereIn('income_id',array_filter(explode(',', $item['type_id'])))->whereIn('status',[0,1,-1])->lockForUpdate()->count();
			if ($apply_count > 0) {
				throw new AppException("ERROR:Data storage exception repeat-- {$item['key_name']}");
			}

            //插入提现收入申请表
            if (!WithdrawIncomeApplyService::insert($withdrawModel)) {
                throw new AppException("ERROR:Data storage exception -- {$item['key_name']}");
            }



            app('plugins')->isEnabled('converge_pay') && $this->withdraw_set['free_audit'] == 1 && $this->pay_way == 'converge_pay' ? \Setting::set('plugin.convergePay_set.notifyWithdrawUrl', Url::shopSchemeUrl('payment/convergepay/notifyUrlWithdraw.php')) : null;
            event(new WithdrawAppliedEvent($withdrawModel));

            $amount = bcadd($amount, $withdrawModel->amounts, 2);

            if (isset($merge_servicetax_withdraw_id)
                && !in_array($item['key_name'], ['StoreCashier', 'StoreWithdraw', 'StoreBossWithdraw'])
                && ($withdrawModel->pay_way != 'balance' || !$this->withdraw_set['balance_special']))
             {  //统计需要劳务税的基本计算金额

                $merge_servicetax_withdraw_id[] = $withdrawModel->id;
                $this_servicetax_amount = !$this->withdraw_set['service_tax_calculation'] ? bcsub($withdrawModel->amounts, $withdrawModel->poundage, 2) : $withdrawModel->amounts;
                if (bccomp($this_servicetax_amount,0,2) != 1) $this_servicetax_amount = 0;
                $merge_servicetax_amount = bcadd($merge_servicetax_amount,$this_servicetax_amount,2);
            }

        }


        if (!empty($merge_servicetax_withdraw_id)){
            $service_tax_data = WithdrawService::getWithdrawServicetaxPercent($merge_servicetax_amount);
            if (bccomp($service_tax_data['servicetax_percent'],0,2) == 1){
                $time = time();
                foreach ($merge_servicetax_withdraw_id as $v){
                    $service_tax_insert_data[] = [
                        'uniacid'=>\YunShop::app()->uniacid,
                        'withdraw_id'=>$v,
                        'servicetax_rate'=>$service_tax_data['servicetax_percent'],
                        'created_at'=>$time,
                        'updated_at'=>$time
                    ];
                }
                WithdrawMergeServicetaxRate::insert($service_tax_insert_data);
            }
        }


        if (bccomp($amount, $this->amount, 2) != 0) {
            throw new AppException('提现失败：提现金额错误');
        }
        return true;
    }


    /**
     * @param $withdraw_item
     * @return array
     * @throws AppException
     */
    private function getWithdrawData($withdraw_item)
    {
        //dd($withdraw_item);
        return [
            'withdraw_sn'       => Withdraw::createOrderSn('WS', 'withdraw_sn'),
            'uniacid'           => \YunShop::app()->uniacid,
            'member_id'         => $this->getMemberId(),
            'type'              => $withdraw_item['type'],
            'type_name'         => $withdraw_item['type_name'],
            'type_id'           => $withdraw_item['type_id'],
            'amounts'           => $withdraw_item['income'],
            'poundage'          => '0.00',
            'poundage_rate'     => '0.00',
            'poundage_type'     => $withdraw_item['poundage_type']?:0,
            'actual_poundage'   => '0.00',
            'actual_amounts'    => '0.00',
            'servicetax'        => '0.00',
            'servicetax_rate'   => '0.00',
            'actual_servicetax' => '0.00',
            'pay_way'           => $this->pay_way,
            'manual_type'       => !empty($this->withdraw_set['manual_type']) ? $this->withdraw_set['manual_type'] : 1,
            'status'            => Withdraw::STATUS_INITIAL,
            'audit_at'          => null,
            'pay_at'            => null,
            'arrival_at'        => null,
            'created_at'        => time(),
            'updated_at'        => time(),
        ];
    }


    /**
     * 提现对应收入设置
     *
     * @param $mark
     * @return array
     */
    private function getIncomeSet($mark)
    {
        return Setting::get('withdraw.' . $mark);
    }


    /**
     * 提现设置
     *
     * @return array
     */
    private function getWithdrawSet()
    {
        return Setting::get('withdraw.income');
    }


    /**
     * @return array
     * @throws AppException
     */
    private function getPostValue()
    {
        $post_data = \YunShop::request()->data;
        Log::info('收入提现提交数据：', [$post_data]);
        //$post_data = $this->testData();

        !is_array($post_data) && $post_data = json_decode($post_data, true);

        if (!$post_data) {
            throw new AppException('Undetected submission of data');
        }
        // 12月20号修改 提现原代码是提现金额不能小于1元
        if ($post_data['total']['amounts'] < 0) {
            throw new AppException('提现金额不能小于0元');
        }

        $amount = $post_data['total']['amounts'];
        $pay_way = $post_data['total']['pay_way'];
        $poundage = $post_data['total']['poundage'];
        $withdraw_data = $post_data['withdrawal'];

        return [$amount, $pay_way, $poundage, $withdraw_data];
    }


    /**
     * @return int
     * @throws AppException
     */
    private function getMemberId()
    {
        $member_id = \YunShop::app()->getMemberId();

        if (!$member_id) {
            throw new AppException('Please log in');
        }
        return $member_id;
    }


    private function testData()
    {
        $data = [
            'total' => [
                'amounts' => 1816.01,
                'poundage' => 181.6,
                'pay_way' => 'balance',
            ],
            'withdrawal' => [
                [
                    'type' => 'Yunshop\ConsumeReturn\common\models\Log',
                    'key_name' => 'consumeReturn',
                    'type_name' => '消费返现',
                    'type_id' => '7223,7319,7408,7477,7605,7680,7808,7881,7973,8048,8137,8205,8274,8401,8535,8670,8721,8805,8877,9030,9145,9237,9325,9403,9477,9554,9755,9837,9919,10012,10101,10184,10374,10528,10650,10760,10858',
                    'income' => '12032.92',
                    'poundage' => '12.03',
                    'poundage_rate' => '0.1',
                    'servicetax' => '1202.08',
                    'servicetax_rate' => '10',
                    'can' => '1',
                    'roll_out_limit' => '0',
                    'selected' => 1,
                ],
                [
                    'type' => 'Yunshop\LevelReturn\models\LevelReturnModel',
                    'key_name' => 'levelReturn',
                    'type_name' => '等级返现',
                    'type_id' => '7426,7481,7556,7883,7884,7885,7886,8222,8223,8224,8281,8360,8552,8895,8954,8955,8956,8957,9107,9621,10598,10599,10784,10785,10786,10989',
                    'income' => '20241.59',
                    'poundage' => '20.24',
                    'poundage_rate' => '0.1',
                    'servicetax' => '2022.13',
                    'servicetax_rate' => '10',
                    'can' => '1',
                    'roll_out_limit' => '10',
                    'selected' => 1,
                ]
            ]
        ];
        return $data;
    }

    private function validateData()
    {
        $member_id = $this->getMemberId();
        //对比提现的收入记录是否属于该会员
        foreach ($this->withdraw_data as $withdraw) {
            $income_ids = array_filter(explode(',', $withdraw['type_id']));
            $income_count = Income::where('member_id', $member_id)->whereIn('id', $income_ids)->count();
			//判断收入是否已提现
			$apply_count = WithdrawIncomeApply::whereIn('income_id',$income_ids)->whereIn('status',[0,1,-1])->count();
            if ($income_count != count($income_ids) || $apply_count > 0) {
                return $this->errorJson('提现数据错误');
            }
        }
    }

    private function validateWithdrawDate()
    {
        $income_set = \Setting::get('withdraw.income');
        $disable = 0;
        $day_msg = '无提现限制';
        if (is_array($income_set['withdraw_date'])) {
            $day = date('d');
            $day_msg = '可提现日期为：'.implode(',',$income_set['withdraw_date']).'号';
            $disable = 1;
            foreach ($income_set['withdraw_date'] as $date) {
                if ($day == $date) {
                    $disable = 0;
                    break;
                }
                if ($day < $date) {
                    $disable = 1;
                }


            }
        }
        if ($disable == 1) {
            return $this->errorJson($day_msg,['status' => 0]);
        }
    }


}
