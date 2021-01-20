<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    12/25/20 2:33 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\backend\modules\point\controllers;


use app\backend\modules\finance\models\PointLog;
use app\common\components\BaseController;
use app\common\services\ExportService;

class ExportController extends BaseController
{
    public function index()
    {
        $exportService = new ExportService($this->exportBuilder(), $this->exportPage());

        $exportData[0] = $this->exportTitle();

        foreach ($exportService->builder_model as $key => $item) {
            $exportData[$key + 1] = [
                $item->created_at,
                $item->member_id,
                $item->member->nickname,
                $item->member->realname,
                $item->member->mobile,
                $item->source_name,
                $item->before_point,
                $item->point,
                $item->after_point,
                $item->remark,
            ];
        }
        $exportService->export($this->fileName(), $exportData, \Request::query('route'));
    }

    /**
     * 导出数据标题
     *
     * @return array
     */
    private function exportTitle()
    {
        return [
            '时间',
            '会员ID',
            '昵称',
            '姓名',
            '手机号',
            '业务类型',
            '原有积分',
            '变动积分',
            '剩余积分',
            '备注'
        ];
    }

    private function exportBuilder()
    {
        $recordsModels = PointLog::uniacid()->with(['member']);

        if ($search = $this->searchParams()) {
            $recordsModels = $recordsModels->search($search);
        }
        return $recordsModels->orderBy('id', 'desc');
    }

    /**
     * @return array
     */
    public function searchParams()
    {
        return request()->search ?: [];
    }

    /**
     * 导出页面页面值
     *
     * @return int
     */
    private function exportPage()
    {
        return request()->export_page ?: 1;
    }

    /**
     * 导出文件名称
     *
     * @return string
     */
    private function fileName()
    {
        return date('Y-m-d-h-i-s', time()) . '积分变动明细导出';
    }
}
