<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/7 下午4:11
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:
 ****************************************************************/

namespace app\frontend\modules\finance\controllers;

use app\backend\modules\withdraw\models\WithdrawRichText;
use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\models\Income;
use app\common\models\MemberShopInfo;
use app\common\services\finance\IncomeService;
use app\framework\Database\Eloquent\Collection;
use app\frontend\modules\finance\models\Withdraw;
use app\frontend\modules\member\models\MemberBankCard;
use Yunshop\ShopEsignV2\common\models\BaseSetting;
use Yunshop\ShopEsignV2\common\models\Company;
use Yunshop\ShopEsignV2\common\models\Scene;
use Yunshop\ShopEsignV2\common\models\ShopContract;
use Yunshop\ShopEsignV2\common\services\CommonService;
use Yunshop\ShopEsignV2\common\services\CreateService;
use Yunshop\TeamDividend\models\TeamDividendAgencyModel;
use Yunshop\TeamDividend\services\withdraw\IncomeWithdrawApply;
use app\common\services\finance\Withdraw as WithdrawService;
use Yunshop\ShopEsign\common\service\ContractService;


class IncomeWithdrawController extends ApiController
{
    //提现设置
    private $withdraw_set;

    //收入设置
    private $income_set;

    //提现方式
    private $pay_way;

    //手续费比例
    private $poundage_rate;

    //手续费类型
    private $poundage_type;

    //劳务税比例
    private $service_tax_rate;

    private $special_poundage_type;

    //
    private $special_poundage_rate;

    //
    private $special_service_tax_rate;

    //提现金额
    private $withdraw_amounts;

    public function preAction()
    {
        parent::preAction();
        $this->setWithdrawSet();

    }

    /**
     * 可提现数据接口【完成】
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWithdraw()
    {
        $shopEsign = $this->shopEsign();

        $income_config = \app\backend\modules\income\Income::current()->getItems();

        $income_data = [];

        $loveName = '爱心值';
        if (app('plugins')->isEnabled('love')) {
            $loveName = LOVE_NAME;

        }
        $deductionLove = [
            'love_name' => $loveName,
            'deduction_type' => 0,
            'deduction_radio' => 0,
            'deduction_value' => 0,
            'deduction_status' => 0,
        ];

        $all_withdraw_income = 0;
        foreach ($income_config as $key => $income) {

            //余额不计算 拍卖预付款不计算
            if ($income['type'] == 'balance' || $income['type'] == 'auction_prepayment') {
                continue;
            }

            //获取收入独立设置
            $this->setIncomeSet($income['type']);

            //附值手续费、劳务税(收银台不计算手续费、劳务税)
            if ($income['type'] == 'kingtimes_provider' || $income['type'] == 'kingtimes_distributor') {
                $this->poundage_rate = 0;
                $this->service_tax_rate = 0;
                $this->special_poundage_rate = 0;
                $this->special_service_tax_rate = 0;
            } else {
                $this->setSpecialPoundageType();
                $this->setPoundageRate($income['type']);
                $this->setServiceTaxRate($income['type']);
                $this->setSpecialPoundageRate();
                $this->setSpecialServiceTaxRate($income['type']);
            }

            $data = $this->getItemData($key, $income);
            if ($data['income'] > 0) {
                $all_withdraw_income += $data['income'];
                $income_data[] = $data;
            }
            //dd($income_data);

            //增加经销商提现显示口爱心值
            if ($income['type'] == 'teamDividend') {
                $teamDividendWithdraw = (new IncomeWithdrawApply());
                $deductionLove['deduction_type'] = $teamDividendWithdraw->deductionType();
                $deductionLove['deduction_radio'] = $teamDividendWithdraw->deductionRadio();
                $deductionLove['deduction_status'] = $teamDividendWithdraw->deductionStatus();
                $deductionLove['deduction_value'] = $teamDividendWithdraw->deductionAmount($this->withdraw_amounts);
            }
        }
        $withdraw_rich_text = WithdrawRichText::uniacid()->first() ?: [];
        $data = [
            'data' => $income_data,
            'setting' => [
                'balance_special' => $this->getBalanceSpecialSet(),
                'service_switch' => $this->withdraw_set['service_switch'] ?: 0,
                'withdraw_rich_text' => $withdraw_rich_text,
                'customer_service' => $this->getCustomerService(),
            ],
            'special_type' => $this->special_poundage_type,
            'deduction_love' => $deductionLove,
            'shop_esign' => $shopEsign,
            'all_withdraw_income' => $all_withdraw_income ?: 0.00,
            'need_sign'  => $this->needSign(),
        ];
        return $this->successJson('获取数据成功!', $data);
    }

    private function needSign()
    {
        if (!app('plugins')->isEnabled('shop-esign-v2')) {
            return false;
        }
        $uid = \YunShop::app()->getMemberId();
        if (CommonService::white($uid)) {
            return false;
        }
        $plugin_set = Setting::get('plugin.shop_esign_v2');
        $scene_list = new Collection();
        if ($plugin_set['withdraw_sign_role_type']) {
            if (!app('plugins')->isEnabled('team-dividend')) {
                return $this->errorJson('经销商插件未开启');
            }
            $level_id = TeamDividendAgencyModel::uniacid()->where('uid', $uid)->value('level');
            $select_uid = CreateService::selectParent($uid);
            if ($select_uid) {
                $cid = Company::uniacid()->where(['uid'=>$select_uid,'status'=>1])->value('cid');
                $base_open_cid = BaseSetting::uniacid()->where('member_id', $select_uid)->where('status', 1)->value('cid');
                if ($base_open_cid == $cid) {
                    $scene_list = Scene::uniacid()->where(['level_id'=>$level_id,'scene_type'=>2,'cid'=>$base_open_cid,'status'=>1])->get();
                }
            }
            if ($scene_list->isEmpty()) {
                $scene_list = Scene::uniacid()->where(['level_id'=>$level_id,'scene_type'=>2,'cid'=>0,'status'=>1])->get();
            }
        }
        if ($scene_list->isEmpty()) {
            $scene_id = $plugin_set['withdraw_scene_id'];
            $scene_list = Scene::where('id', $scene_id)->get();
        }
        if ($scene_list->isEmpty()) {
            return false;
        }
        $contract = ShopContract::uniacid()
            ->where('member_id', \YunShop::app()->getMemberId())
            ->whereIn('scene_id', $scene_list->pluck('id')->all())
            ->where('status', 1)
            ->where('scene_type', 2)
            ->first();
        if (!$contract) {
            return true;
        }
        return false;
    }

    private function getCustomerService()
    {
        $plugin_set = array_pluck(Setting::getAllByGroup('customer-service')->toArray(), 'value', 'key');
        if (app('plugins')->isEnabled('customer-service') && $plugin_set['is_open']) {
            if (request()->type == 2) {
                return [
                    'service' => $plugin_set['mini_link'],
                    'service_QRcode' => yz_tomedia($plugin_set['mini_QRcode']),
                    'service_mobile' => $plugin_set['mini_mobile'],
                    'customer_open' => $plugin_set['mini_open'],
                ];
            }
            return [
                'service' => $plugin_set['link'],
                'service_QRcode' => yz_tomedia($plugin_set['QRcode']),
                'service_mobile' => $plugin_set['mobile'],
            ];
        }
        $shop_set = Setting::get('shop.shop');
        if (request()->type == 2) {
            return [
                'service' => $shop_set['cservice_mini'] ?: '',
            ];
        }
        return ['service' => $shop_set['cservice'] ?: ''];
    }

    public function getMergeServicetax()
    {

        if (!$income_type = request()->income_type) {
            return $this->errorJson('请选择要提现的收入');
        }
        $withdraw_set = \Setting::get('withdraw.income');

        $res = self::getWithdraw();
        $res = json_decode($res->getContent(), true);

        if (!$res) {
            return $this->errorJson('获取收入数据失败');
        }
        $income_data = $res['data']['data'];
        $amount = 0; //劳务费计算基础金额
        $sum_amount = 0; //总提现金额
        $poundage_amount = 0; // 总手续费
        $special_poundage_amount = 0; //余额独立手续费
        $special_tax_amount = 0; //余额独立劳务税
        $set = Setting::get('withdraw.commission');
        foreach ($income_data as $k => $v) {

            if (!in_array($v['key_name'], $income_type)) {
                continue;
            }


            $special_tax_amount = bcadd($special_tax_amount, $v['special_service_tax'], 2);
            $special_poundage_amount = bcadd($special_poundage_amount, $v['special_poundage'], 2);
            if ($set['max_roll_out_limit'] && $set['max_roll_out_limit'] > 0) {
                $v['income'] = isset($v['can_amount']) ? $v['can_amount'] : $v['income'];
            }
            $sum_amount = bcadd($sum_amount, $v['income'], 2);
            $poundage_amount = bcadd($poundage_amount, $v['poundage'], 2);

            if (in_array($v['key_name'], ['StoreCashier', 'StoreWithdraw', 'StoreBossWithdraw'])) {
                continue;
            }

            if (!$withdraw_set['service_tax_calculation']) {
                $this_amount = bcsub($v['income'], $v['poundage'], 2);
                if (bccomp($this_amount, 0, 2) != 1) $this_amount = 0;
            } else {
                $this_amount = $v['income'];
            }
            $amount = bcadd($amount, $this_amount, 2);

        }

        $servicetax_data = WithdrawService::getWithdrawServicetaxPercent($amount);
        return $this->successJson('成功', [
            'sum_amount' => $sum_amount,
            'poundage_amount' => $poundage_amount,
            'servicetax_amount' => $servicetax_data['servicetax_amount'] ?: 0,
            'servicetax_percent' => $servicetax_data['servicetax_percent'] ?: 0,
            'special_tax_amount' => $special_tax_amount,
            'special_poundage_amount' => $special_poundage_amount
        ]);
    }


    public function getLangTitle($data)
    {
        $lang = Setting::get('shop.lang');
        $langData = $lang[$lang['lang']];
        $titleType = '';
        foreach ($langData as $key => $item) {
            $names = explode('_', $key);
            foreach ($names as $k => $name) {
                if ($k == 0) {
                    $titleType = $name;
                } else {
                    $titleType .= ucwords($name);
                }
            }

            if ($data == $titleType) {
                return $item[$key];
            }
        }

    }

    /**
     * @param $income_type
     * @return int|mixed
     */
    private function setPoundageRate($income_type)
    {
        !isset($this->income_set) && $this->income_set = $this->setIncomeSet($income_type);

        $value = array_get($this->income_set, 'poundage_rate', 0);

        $type = array_get($this->income_set, 'poundage_type', 0);

        $this->poundage_type = $type ?: 0;

        return $this->poundage_rate = empty($value) ? 0 : $value;
    }

    /**
     * @return int|mixed
     */
    private function setServiceTaxRate($income_type)
    {
        $value = array_get($this->withdraw_set, 'servicetax_rate', 0);

        if (in_array($income_type, ['StoreCashier', 'StoreWithdraw', 'StoreBossWithdraw'])) {
            $value = 0;
        }
        return $this->service_tax_rate = empty($value) ? 0 : $value;
    }

    /**
     * @param string $incomeType
     * @param float $incomeAmount
     *
     * @return float
     */
    private function getLastServiceTaxRate($incomeType, $incomeAmount)
    {
        if (in_array($incomeType, ['StoreCashier', 'StoreWithdraw', 'StoreBossWithdraw'])) {
            return 0;
        }

        $serviceTaxRateLadder = array_get($this->withdraw_set, 'servicetax', []);
        if (!empty($serviceTaxRateLadder)) {
            $ladderSet = array_column($serviceTaxRateLadder, 'servicetax_money');

            array_multisort($ladderSet, SORT_DESC, $serviceTaxRateLadder);

            foreach ($serviceTaxRateLadder as $value) {
                if ($incomeAmount >= $value['servicetax_money'] && !empty($value['servicetax_money'])) {
                    return $value['servicetax_rate'];
                }
            }
        }
        return array_get($this->withdraw_set, 'servicetax_rate', 0);
    }

    /**
     * 提现到余额独立手续费比例
     * @return int|mixed
     */
    private function setSpecialPoundageRate()
    {
        $value = array_get($this->withdraw_set, 'special_poundage', 0);

        return $this->special_poundage_rate = empty($value) ? 0 : $value;
    }

    /**
     * 提现到余额独立手续费比例
     * @return int|mixed
     */
    private function setSpecialPoundageType()
    {
        $value = array_get($this->withdraw_set, 'special_poundage_type', 0);

        return $this->special_poundage_type = empty($value) ? 0 : $value;
    }

    /**
     * 提现到余额独立劳务税
     * @return int|mixed
     */
    private function setSpecialServiceTaxRate($income_type)
    {
        $value = array_get($this->withdraw_set, 'special_service_tax', 0);

        if (in_array($income_type, ['StoreCashier', 'StoreWithdraw', 'StoreBossWithdraw'])) {
            $value = 0;
        }

        return $this->special_service_tax_rate = empty($value) ? 0 : $value;
    }

    /**
     * 是否使用余额独立手续费、劳务税
     * @return bool
     */
    private function isUseBalanceSpecialSet()
    {
        // if ($this->pay_way == Withdraw::WITHDRAW_WITH_BALANCE &&   这里判断不知道有什么意义，暂时屏蔽
        if (
        $this->getBalanceSpecialSet()
        ) {
            return true;
        }
        return false;
    }

    /**
     * 是否开启提现到余额独立手续费、劳务税
     * @return bool
     */
    private function getBalanceSpecialSet()
    {
        return empty(array_get($this->withdraw_set, 'balance_special', 0)) ? false : true;
    }

    /**
     * 手续费计算公式
     * @param $amount
     * @param $rate
     * @return string
     */
    private function poundageMath($amount, $rate)
    {
        return bcmul(bcdiv($amount, 100, 4), $rate, 2);
    }

    /*
     * 获取收入提现全局设置
     * @return mixed
     */
    private function setWithdrawSet()
    {
        return $this->withdraw_set = Setting::get('withdraw.income');
    }

    /**
     * 获取收入类型独立设置
     * @param $income_type
     * @return mixed
     */
    private function setIncomeSet($income_type)
    {
        return $this->income_set = Setting::get('withdraw.' . $income_type);
    }

    /**
     * @return mixed
     */
    private function getIncomeModel()
    {
        return Income::uniacid()->canWithdraw()
            ->where('member_id', \YunShop::app()->getMemberId());
        //->where('incometable_type', $this->item['class']);
    }

    /**
     * 可提现数据 item
     * @return array
     */
    private function getItemData($key, $income)
    {
        $this->withdraw_amounts = $this->getIncomeModel()->where('incometable_type', $income['class'])->sum('amount');
        $can = $this->incomeIsCanWithdraw();

        if ($income['type'] == 'commission') {
            $max = $this->getWithdrawLog($income['class']);
            $commission_is_can_withdraw = $this->commissionIsCanWithdraw($max);
            if (!$commission_is_can_withdraw['can']) {
                $can = $commission_is_can_withdraw['can'];
                $can_amount = 0;
            } else {
                $can_amount = $commission_is_can_withdraw['can_amount'];
            }
        }
        $withdraw_amounts = isset($can_amount) && $can_amount >0 ? $can_amount : $this->withdraw_amounts;
        //手续费
        $poundage = $this->poundageMath($withdraw_amounts, $this->poundage_rate);
        if ($this->poundage_type == 1) {
            $poundage = number_format($this->poundage_rate, 2, '.', '');
        }
        //劳务税
        //因为增加阶梯劳务税，这里重新赋值付费比例
        $this->service_tax_rate = $this->getLastServiceTaxRate($income['type'], $withdraw_amounts);
        if (array_get($this->withdraw_set, 'service_tax_calculation', 0) == 1) {
            $service_tax = $this->poundageMath($withdraw_amounts, $this->service_tax_rate);
        } else {
            $service_tax = $this->poundageMath($withdraw_amounts - $poundage, $this->service_tax_rate);
        }
        //提现到余额独立手续费
        $special_poundage = $this->poundageMath($withdraw_amounts, $this->special_poundage_rate);
        if ($this->isUseBalanceSpecialSet()) {
            if ($this->special_poundage_type == 1) {
                $special_poundage = number_format($this->special_poundage_rate, 2, '.', '');
            }
        }
        //提现到余额独立劳务税
        if (array_get($this->withdraw_set, 'service_tax_calculation', 0) == 1) {
            $special_service_tax = $this->poundageMath($withdraw_amounts, $this->special_service_tax_rate);
        } else {
            $special_service_tax = $this->poundageMath(($withdraw_amounts - $special_poundage), $this->special_service_tax_rate);
        }


        $actualAmount = bcsub(bcsub($this->withdraw_amounts, $poundage, 2), $service_tax, 2);

        $data = [
            'type' => $income['class'],
            'key_name' => $income['type'],
            'type_name' => $this->getLangTitle($key) ? $this->getLangTitle($key) : $income['title'],
            'income' => $this->withdraw_amounts,
            'poundage' => $poundage,
            'poundage_type' => $this->poundage_type ?: 0,
            'poundage_rate' => $this->poundage_rate,
            'servicetax' => $service_tax,
            'servicetax_rate' => $this->service_tax_rate ?: 0,
            'roll_out_limit' => $this->getIncomeAmountFetter(),
            'max_roll_out_limit' => $this->getIncomeAmountMax(),
            'max_time_out_limit' => $this->getIncomeTimeMax(),
            'can' => $can,
            'selected' => $this->incomeIsCanWithdraw(),
            'type_id' => $this->getIncomeTypeIds($income['class']),
            'special_poundage' => $special_poundage,
            'special_poundage_rate' => $this->special_poundage_rate,
            'special_service_tax' => $special_service_tax,
            'special_service_tax_rate' => $this->special_service_tax_rate,
            'actual_amount' => $actualAmount,
            'income_type' => $this->incomeType($income['type']),
        ];
        if ($income['type'] == 'commission') {
            $data['can_all'] = $this->withdraw_amounts > $this->getIncomeAmountMax() && is_numeric($this->getIncomeAmountMax()) && $can == true;
            $data['can_amount'] = $can_amount;
            $data['residue_amount'] = bcsub($this->withdraw_amounts, $data['can_amount'], 2);
        }
        return $data;
    }


    /**
     * 兼容开发，为了处理门店提现、收银台提现、连锁店提现设置独立的提现打款方式（最快的解决办法，todo 需要优化）
     *
     * @param string $incomeType
     *
     * @return string
     */
    private function incomeType($incomeType)
    {
        //'StoreCashier', 'StoreWithdraw', 'StoreBossWithdraw'
        switch ($incomeType) {
            case 'StoreCashier':
                return 'StoreCashier';
            case 'StoreWithdraw':
                return 'StoreWithdraw';
            case 'StoreBossWithdraw':
                return 'StoreBossWithdraw';
            case 'HotelCashier':
                return 'HotelCashier';
            case 'hotel_withdraw':
                return 'hotel_withdraw';
            default:
                return 'default';
        }
    }

    /**
     * 提现最小额度
     * @return string
     */
    private function getIncomeAmountFetter()
    {
        $value = array_get($this->income_set, 'roll_out_limit', 0);
        return empty($value) ? 0 : $value;
    }

    /**
     * 提现最高额度
     * @return string
     */
    private function getIncomeAmountMax()
    {
        $value = array_get($this->income_set, 'max_roll_out_limit');
        return $value;
    }

    /**
     * 提现最高次数
     * @return string
     */
    private function getIncomeTimeMax()
    {
        $value = array_get($this->income_set, 'max_time_out_limit');
        return $value;
    }

    /**
     * 获取提现记录
     * @return string
     */
    private function getWithdrawLog($class)
    {
        $before_dawn = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $now = time();
        $max_time = Withdraw::where('type', $class)
            ->where('member_id', \YunShop::app()->getMemberId())
            ->whereBetween('created_at', [$before_dawn, $now])
            ->whereIn('status', [1, 2, 4])
            ->count();
        $max_amount = Withdraw::where('type', $class)
            ->where('member_id', \YunShop::app()->getMemberId())
            ->whereBetween('created_at', [$before_dawn, $now])
            ->whereIn('status', [1, 2, 4])
            ->sum('amounts');
        $max = ['max_time' => $max_time, 'max_amount' => $max_amount];

        return $max;
    }

    /**
     * 是否可以提现
     * @return bool
     */
    private function incomeIsCanWithdraw()
    {
        if (bccomp($this->withdraw_amounts, $this->getIncomeAmountFetter(), 2) == -1 || bccomp($this->withdraw_amounts, 0, 2) != 1) {
            return false;
        }
        return true;
    }

    /**
     * 获取 item 对应 id 集
     * @return string
     */
    private function getIncomeTypeIds($income_class)
    {
        if ($this->incomeIsCanWithdraw()) {
            $type_ids = '';
            foreach ($this->getIncomeModel()->where('incometable_type', $income_class)->get() as $ids) {
                $type_ids .= $ids->id . ",";
            }
            return $type_ids;
        }
        return '';
    }





    /************************ todo 杨雷原代码 *********************************/


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIncomeCount()
    {
        $status = \YunShop::request()->status;
        $incomeModel = Income::getIncomes()->where('member_id', \YunShop::app()->getMemberId())->get();
        if ($status >= '0') {
            $incomeModel = $incomeModel->where('status', $status);
        }
        $config = \app\common\modules\shop\ShopConfig::current()->get('plugin');
        $incomeData['total'] = [
            'title' => '推广收入',
            'type' => 'total',
            'type_name' => '推广佣金',
            'income' => $incomeModel->sum('amount')
        ];
        foreach ($config as $key => $item) {

            $typeModel = $incomeModel->where('incometable_type', $item['class']);
            $incomeData[$key] = [
                'title' => $item['title'],
                'ico' => $item['ico'],
                'type' => $item['type'],
                'type_name' => $item['title'],
                'income' => $typeModel->sum('amount')
            ];
            if ($item['agent_class']) {
                $agentModel = $item['agent_class']::{$item['agent_name']}(\YunShop::app()->getMemberId());

                if ($item['agent_status']) {
                    $agentModel = $agentModel->where('status', 1);
                }

                //推广中心显示
                if (!$agentModel) {
                    $incomeData[$key]['can'] = false;
                } else {
                    $agent = $agentModel->first();
                    if ($agent) {
                        $incomeData[$key]['can'] = true;
                    } else {
                        $incomeData[$key]['can'] = false;
                    }
                }
            } else {
                $incomeData[$key]['can'] = true;
            }

        }
        if ($incomeData) {
            return $this->successJson('获取数据成功!', $incomeData);
        }
        return $this->errorJson('未检测到数据!');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIncomeList()
    {
        $configs = \app\backend\modules\income\Income::current()->getItems();
        $type = \YunShop::request()->income_type;
        $search = [];
        foreach ($configs as $key => $config) {
            if ($config['type'] == $type) {
                $search['type'] = $config['class'];
                break;
            }
        }

//        $incomeModel = Income::getIncomeInMonth($search)->where('member_id', \YunShop::app()->getMemberId())->get();
        $incomeModel = Income::getIncomesList($search)->where('member_id', \YunShop::app()->getMemberId())->paginate(20);
        if ($incomeModel) {
            return $this->successJson('获取数据成功!', $incomeModel);
        }
        return $this->errorJson('未检测到数据!');
    }

    /**
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function getDetail()
    {
        $data = "";
        $id = \YunShop::request()->id;
        $detailModel = Income::getDetailById($id);
        if ($detailModel) {
            if ($detailModel->first()->detail != '') {
                $data = $detailModel->first()->detail;
                return '{"result":1,"msg":"成功","data":' . $data . '}';
            }
            return '{"result":1,"msg":"成功","data":""}';
        }
        return $this->errorJson('未检测到数据!');
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSearchType()
    {
        $configs = \app\backend\modules\income\Income::current()->getItems();
        foreach ($configs as $key => $config) {
            if ($config['type'] == 'balance') {
                continue;
            }
            $searchType[] = [
                'title' => $config['title'],
                'type' => $config['type']
            ];
        }
        if ($searchType) {
            return $this->successJson('获取数据成功!', $searchType);
        }
        return $this->errorJson('未检测到数据!');
    }


    /**
     * 获取收入提现按钮开关
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIncomeWithdrawMode()
    {
        $incomeWithdrawMode = (new IncomeService())->withdrawButton(request()->income_type);
        if ($incomeWithdrawMode) {
            if ($incomeWithdrawMode['manual']) {
                $member_id = \YunShop::app()->getMemberId();
                $yz_member = MemberShopInfo::uniacid()->select(['alipay','wechat'])->where('member_id', $member_id)->first();
                $member_bank = MemberBankCard::uniacid()->select(['member_name','bank_card'])->where('member_id', $member_id)->first();
                if ($member_bank->bank_card) {
                    $member_bank->bank_card = $member_bank->bank_card ? substr_replace($member_bank->bank_card,'******',6,-4): '';
                }
                $incomeWithdrawMode['manual']['manual_type'] = $this->withdraw_set['manual_type'];
                $incomeWithdrawMode['manual']['alipay'] = $yz_member->alipay;
                $incomeWithdrawMode['manual']['wechat'] = $yz_member->wechat;
                $incomeWithdrawMode['manual']['member_name'] = $member_bank->member_name;
                $incomeWithdrawMode['manual']['bank_card'] = $member_bank->bank_card;
            }
            return $this->successJson('获取数据成功!', $incomeWithdrawMode);
        }

        return $this->errorJson('未检测到数据!');
    }

    /**
     * @return bool
     * 电子合同验证
     */
    private function shopEsign()
    {
        if (app('plugins')->isEnabled('shop-esign')) {
            $data = ContractService::checkNeedSign();
            if ($data) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 分销商能否提现验证
     * @return array
     */
    private function commissionIsCanWithdraw($max)
    {
        $set = Setting::get('withdraw.commission');
        $incomes = $this->getIncomeModel()->where('incometable_type', 'Yunshop\Commission\models\CommissionOrder')->orderBy('amount', 'desc')->get()->toArray();
        $amount = $this->getToDayAmount();
        $amount_c = $this->getToDayAmount();
        $can_amount = 0;
        $income_ids = [];
        foreach ($incomes as $item) {
            if ($amount > $set['max_roll_out_limit']) {
                break;
            }
            $amount_c = bcadd($item['amount'], $amount_c, 2);;
            if ($amount_c > $set['max_roll_out_limit']) {
                $amount_c = $amount;
                continue;
            }
            $amount = $amount_c;
            $can_amount = bcadd($item['amount'], $can_amount, 2);;
            $income_ids[] = $item['id'];
        }

        if (is_numeric($this->getIncomeAmountMax()) || is_numeric($this->getIncomeTimeMax())) {
            if (!is_numeric($this->getIncomeAmountMax())) {
                if ($max['max_time'] >= $this->getIncomeTimeMax()) {
                    return ['can' => false];
                }
            } elseif (!is_numeric($this->getIncomeTimeMax())) {
                if (count($income_ids) < 1) {
                    return ['can' => false];
                }
            } else {
                if ($max['max_time'] >= $this->getIncomeTimeMax()) {
                    return ['can' => false];
                } elseif (count($income_ids) < 1) {
                    return ['can' => false];
                }
            }
            if ($can_amount + $this->getToDayAmount() > $this->getIncomeAmountMax()) {
                return ['can' => false];
            }
        }
        return ['can' => true, 'can_amount' => $can_amount];
    }

    private function getToDayAmount()
    {
        $before_dawn = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $now = time();
        $max_amount = Withdraw::where('type', 'Yunshop\Commission\models\CommissionOrder')
            ->where('member_id', \YunShop::app()->getMemberId())
            ->whereBetween('created_at', [$before_dawn, $now])
            ->whereIn('status', [0, 1, 2, 4])
            ->sum('amounts');

        return $max_amount ?: 0;
    }
}
