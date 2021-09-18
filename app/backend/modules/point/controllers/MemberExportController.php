<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    12/25/20 4:11 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\backend\modules\point\controllers;


use app\backend\modules\member\models\Member;
use app\common\components\BaseController;
use app\common\services\ExportService;

class MemberExportController extends BaseController
{
    public function index()
    {
        $exportService = new ExportService($this->exportBuilder(), $this->exportPage());

        $exportData[0] = $this->exportTitle();
        
        foreach ($exportService->builder_model as $key => $item) {
            $exportData[$key + 1] = [
                date('Y-m-d H:i:s', $item->createtime),
                $item->uid,
                strpos($item->nickname,'=') === 0 ? ' ' . $item->nickname : $item->nickname,
                strpos($item->realname,'=') === 0 ? ' ' . $item->realname : $item->realname,
                $item->mobile,
                $item->credit1,
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
            '积分',
        ];
    }

    private function exportBuilder()
    {
        $recordsModels = Member::searchMembers(\YunShop::request(), 'credit1');
        return $recordsModels->orderBy('uid', 'desc');
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
        return date('Y-m-d-h-i-s', time()) . '会员积分导出';
    }
}
