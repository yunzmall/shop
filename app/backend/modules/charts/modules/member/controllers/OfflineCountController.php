<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/10/16 下午7:03
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:
 ****************************************************************/

namespace app\backend\modules\charts\modules\member\controllers;

use app\backend\modules\charts\modules\member\models\MemberLowerCount;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;

class OfflineCountController extends BaseController
{
    public function index()
    {
        $pageSize = 10;
        $search = \YunShop::request()->search;

        $list = MemberLowerCount::getMember($search)->orderBy('team_total', 'desc')->paginate($pageSize);
        $page = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());
        return view('charts.member.offline_count', [
            'page_size'=>$pageSize,
            'this_page'=>empty(\YunShop::request()->page) ? 1 : \YunShop::request()->page,
            'page' => $page,
            'search' => $search,
            'list' => $list,
        ])->render();
    }

    public function update()
    {
        (new \app\backend\modules\charts\modules\member\services\TimedTaskService())->handle();
        return $this->successJson('成功');
    }
}
