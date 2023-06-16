<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/11/14 上午10:22/2019-06-28 下午14:47
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:     
 * Tool:    Created by PhpStorm.
 ****************************************************************/

namespace app\backend\modules\withdraw\controllers;


use app\backend\modules\withdraw\models\WithdrawModel as Withdraw;
use app\backend\modules\member\models\MemberBankCard;
use app\backend\modules\member\models\MemberShopInfo;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use app\common\models\WithdrawMergeServicetaxRate;
use app\common\services\ExportService;

class RecordsController extends BaseController
{
    /**
     * @var Withdraw
     */
    private $withdrawModel;
    private $amount;

    public function __construct()
    {
        parent::__construct();

        $this->withdrawModel = Withdraw::records();
    }

    //全部记录
    public function index()
    {
        $this->searchRecords();

        return $this->isExport() ? $this->export() : $this->view();
    }

    //待审核记录
    public function initial()
    {
        $this->withdrawModel->initial();

        return $this->index();
    }

    //待打款记录
    public function audit()
    {
        $this->withdrawModel->audit();

        return $this->index();
    }

    //打款中记录
    public function paying()
    {
        $this->withdrawModel->paying();

        return $this->index();
    }

    //已打款记录
    public function payed()
    {
        $this->withdrawModel->payed();

        return $this->index();
    }

    //已驳回记录
    public function rebut()
    {
        $this->withdrawModel->rebut();

        return $this->index();
    }

    //已无效记录
    public function invalid()
    {
        $this->withdrawModel->invalid();

        return $this->index();
    }

    /**
     * 视图和页面数据
     *
     */
    private function view()
    {
        if (request()->ajax()) {
            return $this->successJson('ok', $this->resultData());
        }
        return view('withdraw.records');
    }

    /**
     * 导出 Excel
     */
    private function export()
    {
        return $this->_export();
    }

    /**
     * @return array
     */
    private function resultData()
    {
        $records = $this->withdrawModel->paginate()->toArray();

        $shopSet = Setting::get('shop.member');
        foreach ($records['data'] as &$item) {
            $item['has_one_member']['avatar'] =  $item['has_one_member']['avatar'] ? tomedia($item['has_one_member']['avatar'] ) : tomedia($shopSet['headimg']);
            $item['has_one_member']['nickname'] = $item['has_one_member']['nickname'] ?: '未更新';
        }

        return [
            'records' => $records,
            'search' => $this->searchParams(),
            'types' => Withdraw::getTypes(),
            'pay_way_list' => Withdraw::getPayWay(),
            'amount' => $this->amount
        ];
    }

    /**
     * 记录搜索
     */
    private function searchRecords()
    {    
        $search = $this->searchParams();
        if ($search) {
            $search['searchtime'] = is_numeric($search['time']['start']) && is_numeric($search['time']['end']);
            $this->withdrawModel->search($search);
        }
        $this->withdrawModel->orderBy('created_at', 'desc');
        $this->amount = $this->withdrawModel->sum('amounts');
    }

    /**
     * @return array
     */
    private function searchParams()
    {
        $search = \YunShop::request()->search;

        return $search ?: [];
    }

    /**
     * @return bool
     */
    private function isExport()
    {
        $isExport = \YunShop::request()->export;

        return $isExport ? true : false;
    }

    /**
     * 导出Excel
     */
    private function _export()
    {
        $records = $this->withdrawModel;

        $export_page = request()->export_page ? request()->export_page : 1;
        $export_model = new ExportService($records, $export_page);

        $file_name = date('Ymdhis', time()) . '提现记录导出';

        $export_data[0] = [
            '提现编号',
            '会员ID',
            '粉丝',
            '姓名、手机',
            '收入类型',
            '提现方式',
            '申请金额',

            '手续费',
            '劳务税',
            '应打款金额',

            '申请时间',
            '审核时间',
            '打款时间',
            '到账时间',
            '自定义信息',


            '打款至',

            '打款微信号',

            '支付宝姓名',
            '支付宝账号',

            '开户行',
            '开户行省份',
            '开户行城市',
            '开户行支行',
            '银行卡信息',
            '开户人姓名',
        ];
        foreach ($export_model->builder_model as $key => $item)
        {
            $nickname = $item->hasOneMember->nickname;
            $realname = $item->hasOneMember->realname.'/'.$item->hasOneMember->mobile;
            $export_data[$key + 1] = [
                $item->withdraw_sn,
                $item->member_id,
                strpos($nickname,'=') === 0 ? ' ' . $nickname : $nickname,
                strpos($realname,'=') === 0 ? ' ' . $realname : $realname,
                $item->type_name,
                $item->pay_way_name,
                $item->amounts,
                $this->getEstimatePoundage($item),//$item->actual_poundage,
                ($item->type == 'balance'?0:$this->getEstimateServiceTax($item)),//$item->actual_servicetax,
//                $item->actual_amounts,
                $this->getActualAmount($item),
                $item->created_at->toDateTimeString(),
                $item->audit_at ? $item->audit_at->toDateTimeString() : '',
                $item->pay_at ? $item->pay_at->toDateTimeString() : '',
                $item->arrival_at ? $item->arrival_at->toDateTimeString() : '',
                $this->getCustomValue($item->member_id),
            ];
            switch ($item->manual_type) {
                case 2:
                    $export_data[$key + 1][] = '微信';
                    $export_data[$key + 1] = array_merge($export_data[$key + 1], $this->getMemberWeChat($item->member_id));
                    break;
                case 3:
                    $export_data[$key + 1][] = '支付宝';
                    $export_data[$key + 1] = array_merge($export_data[$key + 1], $this->getMemberAlipay($item->member_id));
                    break;
                default:
                    $export_data[$key + 1][] = '银行卡';
                    $export_data[$key + 1] = array_merge($export_data[$key + 1], $this->getMemberBankCard($item->member_id));
                    break;
            }

            //判断字体，针对性的防止𠂆字，使xsl终止不完整的bug
            $zit = strpos($export_data[$key + 1][21],'𠂆');
            if ($zit) {
                $export_data[$key + 1][21] = '*';
            }
        }
        $export_model->export($file_name, $export_data, \Request::query('route'));
    }

    private function getMemberAlipay($member_id)
    {
        $yzMember = MemberShopInfo::select('alipayname','alipay')->where('member_id',$member_id)->first();
        return $yzMember ? [ '', $yzMember->alipayname ?: '', $yzMember->alipay ?: '' ] : ['', ''];
    }

    private function getMemberWeChat($member_id)
    {
        $yzMember = MemberShopInfo::select('wechat')->where('member_id',$member_id)->first();
        return $yzMember ? [ $yzMember->wechat ?: '' ] : [''];
    }

    private function getMemberBankCard($member_id)
    {
        $bankCard = MemberBankCard::where('member_id',$member_id)->first();
        if ($bankCard) {
            return [
                '', '', '',
                $bankCard->bank_name ?: '',
                $bankCard->bank_province ?: '',
                $bankCard->bank_city ?: '',
                $bankCard->bank_branch ?: '',
                $bankCard->bank_card ? $bankCard->bank_card . ",": '',
                $bankCard->member_name ?: ''
            ];
        }
        return ['','','','','','','','',''];
    }

    private function getEstimatePoundage($item)
    {
        if (!(float)$item->actual_poundage > 0 || is_null($item->actual_poundage)) {
            return bcdiv(bcmul($item->amounts, $item->poundage_rate, 4), 100, 2);
        }
        return $item->actual_poundage;
    }

    private function getEstimateServiceTax($withdraw)
    {
//        if (!(float)$item->actual_servicetax > 0 || is_null($item->actual_servicetax)) {
//            $poundage = $this->getEstimatePoundage($item);
//            $amount = bcsub($item->amounts, $poundage, 2);
//            return bcdiv(bcmul($amount, $item->servicetax_rate, 4), 100, 2);
//        }
        $withdraw->servicetax = $this->setWithdraw($withdraw)->servicetax;

        return $withdraw->servicetax;
    }

    private function getCustomValue($member_id)
    {
        $yzMember = MemberShopInfo::select('member_form')->where('member_id',$member_id)->first();

        $str = '';
        if($yzMember->member_form){
            foreach(json_decode($yzMember->member_form) as $value){
               $str .= '<'.$value->name.':'.$value->value.'>     ';
            }
        }
        return $str;
    }

    public function getActualAmount($withdraw)
    {
        $withdraw_data = $this->setWithdraw($withdraw);
        if ($withdraw_data->type == 'balance')  {//余额不减劳务税
            $withdraw_data->actual_amounts = bcsub($withdraw_data->amounts, $withdraw_data->poundage, 2);
        }

        // 暂时屏蔽, 等之后有误重新计算
//        else {
//            $withdraw_data->actual_amounts = bcsub(bcsub($withdraw_data->amounts, $withdraw_data->poundage, 2), $withdraw_data->servicetax, 2);
//        }
        return $withdraw_data->actual_amounts;
    }

    private function setWithdraw($withdraw)
    {
        if ($withdraw->status == 0) {
            $withdraw_set = \Setting::get('withdraw.income');
            if ($withdraw->pay_way == 'balance' && $withdraw_set['balance_special']) {
                $merge_percent = null;
            } else {
                $merge_percent = WithdrawMergeServicetaxRate::uniacid()->where('withdraw_id', $withdraw->id)->where('is_disabled', 0)->first();
            }
            if ($merge_percent) {
                $withdraw->servicetax_rate = $merge_percent->servicetax_rate;
                $base_amount = !$withdraw_set['service_tax_calculation'] ? bcsub($withdraw->amounts, $withdraw->poundage, 2) : $withdraw->amounts;
                $withdraw->servicetax = bcmul($base_amount, bcdiv($withdraw->servicetax_rate, 100, 4), 2);
            } elseif ($withdraw->pay_way != 'balance' || !$withdraw_set['balance_special']) {
                $base_amount = !$withdraw_set['service_tax_calculation'] ? bcsub($withdraw->amounts, $withdraw->poundage, 2) : $withdraw->amounts;
                $res = \app\common\services\finance\Withdraw::getWithdrawServicetaxPercent($base_amount,$withdraw);
                $withdraw->servicetax_rate = $res['servicetax_percent'];
                $withdraw->servicetax = $res['servicetax_amount'];
            }
        }
        return $withdraw;
    }

}
