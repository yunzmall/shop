<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/5/15 上午9:51
 * Email: livsyitian@163.com
 */

namespace app\backend\modules\income\controllers;


use app\backend\modules\income\models\Income;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use app\common\services\ExportService;

class IncomeRecordsController extends BaseController
{

    //收入明细
    public function index()
    {
        if (request()->ajax()) {
            $records = Income::records()->withMember();

            $search = \YunShop::request()->search;
            if ($search) {
                $records = $records->search($search)->searchMember($search);
            }
            $pageList = $records->orderBy('id', 'desc')->paginate();
            $amount = $records->sum('amount');
            $shopSet = Setting::get('shop.member');
            $pageList->map(function ($item) {
                $item->member->nickname = $item->member->nickname ?:
                    ($item->member->mobile ? substr($item->member->mobile, 0, 2) . '******' . substr($item->member->mobile, -2, 2) : '无昵称会员');
            });
            $pageList = $pageList->toArray();
            foreach ($pageList['data'] as &$item) {
                if (!$item['member']) {
                    $item['member'] = [
                        'nickname' => '已注销或已删除会员',
                        'avatar' => tomedia($shopSet['headimg']),
                    ];
                }
            }
            return $this->successJson('ok', [
                'pageList' => $pageList,
                'search' => $search,
                'income_type_comment' => $this->getIncomeTypeComment(),
                'amount' => $amount
            ]);
        }
        return view('income.income_records')->render();

    }

    //收入明细导出excel
    public function export()
    {
        $records = Income::records()->withMember()->orderBy('created_at', 'desc');
        $search = \YunShop::request()->search;
        if ($search) {
            if (isset($search['time'])) {
                $search['time'] = explode(',', $search['time']);
                $search['time'] = [
                    'start' => $search['time'][0],
                    'end' => $search['time'][1]
                ];
            }
            $records = $records->search($search)->searchMember($search);
        }

        $export_page = request()->export_page ? request()->export_page : 1;
        $export_model = new ExportService($records, $export_page);
//        $data = $records->orderBy('created_at','desc')->limit(20)->get()->toArray();

        $excel_data=[['会员id','粉丝','姓名','手机','时间','收入金额','业务类型','提现状态','打款状态']];
//        foreach ($data as $v){
        foreach ($export_model->builder_model->toArray() as $v){
            $excel_data[]=[
                $v['member_id'],
                empty($v['member']['nickname']) ? '' : $v['member']['nickname'],
                empty($v['member']['realname']) ? '' : $v['member']['realname'],
                empty($v['member']['mobile']) ? '' : $v['member']['mobile'],
                $v['created_at'],
                $v['amount'],
                $v['type_name'],
                $v['status_name'],
                $v['pay_status_name']
            ];
        }
        $file_name = date('Ymdhis', time()) . '收入明细导出'.$export_page;
        $export_model->export($file_name, $excel_data, "income.income-records.index");

    }


    private function getIncomeTypeComment()
    {
        return \app\backend\modules\income\Income::current()->getItems();
    }




}
