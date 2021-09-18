<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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
