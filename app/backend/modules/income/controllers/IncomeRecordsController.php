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
use app\common\helpers\PaginationHelper;
use app\common\services\ExportService;

class IncomeRecordsController extends BaseController
{

    //收入明细
    public function index()
    {
        $records = Income::records()->withMember();

        $search = \YunShop::request()->search;
        if ($search) {
            //dd($search);
            $records = $records->search($search)->searchMember($search);
        }

        $pageList = $records->orderBy('created_at','desc')->paginate();
        $page = PaginationHelper::show($pageList->total(),$pageList->currentPage(),$pageList->perPage());

        return view('income.income_records',[
            'pageList'          => $pageList,
            'page'              => $page,
            'search'            => $search,
            'income_type_comment' => $this->getIncomeTypeComment()
        ])->render();

    }

    //收入明细导出excel
    public function export(){
        $records = Income::records()->withMember()->orderBy('created_at','desc');
        $search = \YunShop::request()->search;
        if ($search) {
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
