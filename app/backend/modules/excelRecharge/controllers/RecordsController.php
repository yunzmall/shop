<?php
/**
 * Created by PhpStorm.
 * User: king/QQ:995265288
 * Date: 2019-06-19
 * Time: 16:49
 */

namespace app\backend\modules\excelRecharge\controllers;


use app\backend\models\excelRecharge\RecordsModel;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;

class RecordsController extends BaseController
{
    /**
     * @var RecordsModel
     */
    protected $recordsModels;


    //会员excel充值记录
    public function index()
    {
        if (request()->ajax()) {
            $this->recordsModels = $this->pageList();
            return $this->successJson('ok', $this->resultData());
        }

        return view('excelRecharge.records');
    }

    private function resultData()
    {
        return [
            'pageList' => $this->recordsModels
        ];
    }

    private function page()
    {
        return PaginationHelper::show($this->recordsModels->total(), $this->recordsModels->currentPage(), $this->recordsModels->perPage());
    }

    /**
     * @return RecordsModel
     */
    private function pageList()
    {
        $records = RecordsModel::orderBy('created_at', 'desc');

        return $records->paginate('', ['*'], '', $this->pageParam());
    }

    /**
     * @return int
     */
    private function pageParam()
    {
        return (int)request()->page ?: 1;
    }

}
