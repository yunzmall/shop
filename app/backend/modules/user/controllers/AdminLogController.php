<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 22/04/2021
 * Time: 16:03
 */

namespace app\backend\modules\user\controllers;

use app\common\components\BaseController;
use app\common\models\user\YzAdminLog;


class AdminLogController extends BaseController
{
    public function index()
    {
        if (request()->ajax()) {
            $pageSize = 10;
            $search = request()->search;
            $list = YzAdminLog::getPageList($pageSize, $search);

            $admin_type = ['超级管理员', '操作员', '供应商', '门店', '酒店', '区域代理', '自提点', '分公司'];

            return $this->successJson('请求接口成功', [
                'list' => $list,
                'search' => $search,
                'adminType' => $admin_type
            ]);
        }
        return view('user.user.admin_log')->render();
    }

    public function del()
    {
        $start = request()->start;
        $end = request()->end;
        if (empty($start) || empty($end)) {
            return json_encode(['result' => 0, 'msg' => '时间不能为空']);
        }

        $del = YzAdminLog::del($start, $end)->delete();

        if ($del) {
            return $this->successJson('删除成功');
        }
        return json_encode(['result' => 0, 'msg' => '删除失败']);

    }
}
