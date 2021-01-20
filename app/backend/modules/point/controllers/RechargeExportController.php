<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    12/28/20 9:57 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\backend\modules\point\controllers;


use app\backend\modules\point\models\RechargeModel;
use app\common\components\BaseController;
use app\common\services\ExportService;

class RechargeExportController extends BaseController
{
    public function index()
    {
        $exportService = new ExportService($this->exportBuilder(), $this->exportPage());

        $exportData[0] = $this->exportTitle();

        foreach ($exportService->builder_model as $key => $item) {
            $exportData[$key + 1] = [
                $item->created_at,
                $item->member->uid,
                $item->member->nickname,
                $item->member->realname,
                $item->member->mobile,
                $item->order_sn,
                $item->money,
                $item->type_name,
                $item->status == 1 ? "充值成功" : "充值失败",
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
            '充值时间',
            '会员ID',
            '昵称',
            '姓名',
            '手机号',
            '充值单号',
            '充值积分',
            '充值方式',
            '充值状态',
            '充值备注',
        ];
    }

    private function exportBuilder()
    {
        $recordsModels = RechargeModel::uniacid()->with('member');

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
        return date('Y-m-d-h-i-s', time()) . '会员积分充值记录导出';
    }
}
