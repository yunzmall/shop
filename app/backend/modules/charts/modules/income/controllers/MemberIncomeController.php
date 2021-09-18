<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2018/10/16
 * Time: 16:48
 */

namespace app\backend\modules\charts\modules\income\controllers;


use app\backend\modules\member\models\Member;
use app\common\components\BaseController;
use app\backend\modules\finance\models\Withdraw;
use app\common\helpers\PaginationHelper;
use app\backend\modules\charts\models\Income;
use app\common\services\ExportService;

class MemberIncomeController extends BaseController
{
    /**
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        $search = \Yunshop::request()->search;

        $pageSize = 10;
        $page = request()->page;
        $list = Income::search($search)
            ->selectRaw('sum(amount) as total_amount, sum(if(status=1,amount,0)) as withdraw, sum(if(status=0,amount,0)) as unwithdraw, member_id')
            ->selectRaw('sum(if(incometable_type like "%AreaDividend", amount, 0)) as area_dividend')
            ->selectRaw('sum(if(incometable_type like "%CommissionOrder", amount, 0)) as commission_dividend')
            ->selectRaw('sum(if(incometable_type like "%MerchantBonusLog", amount, 0)) as merchant_dividend')
            ->selectRaw('sum(if(incometable_type like "%ShareholderDividendModel", amount, 0)) as shareholder_dividend')
            ->selectRaw('sum(if(incometable_type like "%TeamDividend%", amount, 0)) as team_dividend')
            ->with([
                'hasOneWithdraw' => function($q) {
                    $q->selectRaw('sum(poundage) as total_poundage, member_id')->groupBy('member_id');
                },
                'hasOneMember',
            ])
            ->groupBy('member_id')
            ->orderBy('total_amount', 'desc')
            ->orderBy('member_id', 'asc')
            ->paginate($pageSize);

        //总统计
        $total = Income::search($search)
            ->selectRaw('sum(amount) as total_amount, sum(if(status=1,amount,0)) as withdraw, sum(if(status=0,amount,0)) as unwithdraw')
            ->selectRaw('sum(if(incometable_type like "%AreaDividend", amount, 0)) as area_dividend')
            ->selectRaw('sum(if(incometable_type like "%CommissionOrder", amount, 0)) as commission_dividend')
            ->selectRaw('sum(if(incometable_type like "%MerchantBonusLog", amount, 0)) as merchant_dividend')
            ->selectRaw('sum(if(incometable_type like "%ShareholderDividendModel", amount, 0)) as shareholder_dividend')
            ->selectRaw('sum(if(incometable_type like "%TeamDividend%", amount, 0)) as team_dividend')
            ->first()
            ->toArray();
        $totalPoundage = \app\common\models\Withdraw::uniacid()->selectRaw('sum(actual_poundage) as total_poundage')->first();

        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());
        return view('charts.income.member_income',[
            'list' => $list,
            'pager' => $pager,
            'page' => request()->page > 1? request()->page - 1 : '',
            'total' => $total,
            'search' => $search,
            'totalPoundage' => $totalPoundage
        ])->render();
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function detail()
    {
//        $groups = MemberGroup::getMemberGroupList();
//        $levels = MemberLevel::getMemberLevelList();
        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;
        if ($uid == 0 || !is_int($uid)) {
            $this->message('参数错误', '', 'error');
            exit;
        }

        $member = Member::whereUid($uid)->first()->toArray();

        if (empty($member)) {
            return $this->message('会员不存在');
        }

        //检测收入数据
        $incomeModel = Income::getIncomes()->where('member_id', $uid)->orderBy('created_at','decs');
//        dd($incomeModel);
//        $config = \app\backend\modules\income\Income::current()->getItems();
//        unset($config['balance']);
        $incomeData = $incomeModel->paginate(10);
        $incomeAll = [
//            'title' => '推广收入',
//            'type' => 'total',
//            'type_name' => '推广佣金',
            'income' => $incomeModel->sum('amount'),
            'withdraw' => $incomeModel->where('status', 1)->sum('amount'),
            'no_withdraw' => $incomeModel->where('status', 0)->sum('amount')
        ];
//        $incomeData = $incomeModel->orderBy('id', 'desc')->paginate(10);

        $pager = PaginationHelper::show($incomeData->total(), $incomeData->currentPage(), $incomeData->perPage());
//
//        $incomeData = [];
//        foreach ($config as $key => $item) {
//
//            $typeModel = $incomeModel->where('incometable_type', $item['class']);
//            $incomeData[$key] = [
//                'title' => $item['title'],
//                'ico' => $item['ico'],
//                'type' => $item['type'],
//                'type_name' => $item['title'],
//                'income' => $typeModel->sum('amount'),
//                'withdraw' => $typeModel->where('status', 1)->sum('amount'),
//                'no_withdraw' => $typeModel->where('status', 0)->sum('amount')
//            ];
//        }

        return view('charts.income.member_income_detail', [
            'member' => $member,
            'incomeAll' => $incomeAll,
            'item' => $incomeData,
            'pager' => $pager
        ])->render();
    }

    public function export()
    {
        $search = \YunShop::request()->search;
        $builder = Income::search($search)
            ->selectRaw('sum(amount) as total_amount, sum(if(status=1,amount,0)) as withdraw, sum(if(status=0,amount,0)) as unwithdraw, member_id')
            ->selectRaw('sum(if(incometable_type like "%AreaDividend", amount, 0)) as area_dividend')
            ->selectRaw('sum(if(incometable_type like "%CommissionOrder", amount, 0)) as commission_dividend')
            ->selectRaw('sum(if(incometable_type like "%MerchantBonusLog", amount, 0)) as merchant_dividend')
            ->selectRaw('sum(if(incometable_type like "%ShareholderDividendModel", amount, 0)) as shareholder_dividend')
            ->selectRaw('sum(if(incometable_type like "%TeamDividend%", amount, 0)) as team_dividend')
            ->with([
                'hasOneWithdraw' => function($q) {
                    $q->selectRaw('sum(poundage) as total_poundage, member_id')->groupBy('member_id');
                },
                'hasOneMember',
            ])
            ->groupBy('member_id')
            ->orderBy('total_amount', 'desc')
            ->orderBy('member_id', 'asc')->get();
        $file_name = date('Ymdhis', time()) . '会员收入统计导出';
        $export_data[0] = ['排行', '会员', '累计收入', '未提现收入', '已提现收入', '扣除手续费', '分销佣金', '经销商提成', '股东分红', '区域分红','招商分红'];
        foreach ($builder as $key => $item) {
            $nickname = strpos($item->hasOneMember->nickname,'=') === 0 ? ' ' . $item->hasOneMember->nickname : $item->hasOneMember->nickname;
            $export_data[$key + 1] = [
                $key + 1,
                $nickname ?: '未更新',
                $item->total_amount?: '0.00',
                $item->unwithdraw ?: '0.00',
                $item->withdraw?: '0.00',
                $item->hasOneWithdraw->total_poundage?: '0.00',
                $item->commission_dividend,
                $item->team_dividend ?: '0.00',
                $item->shareholder_dividend ?: '0.00',
                $item->area_dividend ?: '0.00',
                $item->merchant_dividend ?: '0.00',
            ];
        }
        \Excel::create($file_name, function ($excel) use ($export_data) {
            $excel->sheet('score', function ($sheet) use ($export_data) {
                $sheet->rows($export_data);
            });
        })->export('csv');

    }

}