<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/2/13
 * Time: 15:41
 */

namespace app\backend\modules\member\controllers;


use app\common\components\BaseController;
use app\common\models\member\MemberInvitationCodeLog;
use app\common\helpers\PaginationHelper;
use app\common\services\ExportService;
use app\common\models\MemberShopInfo;


class MemberInvitedController extends BaseController
{
    /**
     * 加载模板
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        return view('member.invited', [])->render();
    }

    public function show()
    {
        $search = \YunShop::request()->search;
        $list =  MemberInvitationCodeLog::searchLog($search)
        ->orderBy('id', 'desc')
        ->groupBy('member_id')
        ->paginate()
        ->toArray();

        return $this->successJson('ok', [
            'list'=>$list,
            'search'=>$search
        ]);
    }

    public function export()
    {
        $member_builder = MemberInvitationCodeLog::searchLog(\YunShop::request()->search);
        $export_page = request()->export_page ? request()->export_page : 1;

        $export_model = new ExportService($member_builder, $export_page);
        $file_name = date('Ymdhis', time()) . '邀请码使用情况导出';

        $export_data[0] = ['ID', '邀请人id', '被邀请人id', '邀请码', '注册时间'];

        $list = $export_model->builder_model->toArray();

        if ($list) {

            foreach ($list as $key => $item) {
                $export_data[$key + 1] = [$item['id'], $item['mid'], $item['member_id'], $item['invitation_code'],
                    $item['created_at']
                ];
            }
         // 此处参照商城订单管理的导出接口
            app('excel')->store(new \app\exports\FromArray($export_data),$file_name.'.xlsx','export');
            app('excel')->download(new \app\exports\FromArray($export_data),$file_name.'.xlsx')->send();

        } else {
            return $this->message('暂无数据', yzWebUrl('member.member-invited.index'));
        }
    }
}