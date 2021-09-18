<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2021-03-04
 * Time: 14:54
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))     梦之所想,心之所向.
 */

namespace app\backend\modules\member\controllers;


use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\models\member\MemberChangeLog;
use app\common\models\member\MemberMergeLog;
use app\common\models\SynchronizedBinder;

class MergeLogController extends BaseController
{
    public function authMerge()
    {
        $search = request()->search;
        $list = MemberChangeLog::searchLog($search)->orderBy('created_at', 'desc')->paginate()->toArray();
        $page = PaginationHelper::show($list['total'], $list['current_page'], $list['per_page']);

        return view('member.mergeLog.auth-merge', [
            'search' => $search,
            'list' => $list,
            'page' => $page,
        ]);
    }

    public function bindTel()
    {
        $search = request()->search;
        $list = SynchronizedBinder::searchLog($search)->orderBy('created_at', 'desc')->paginate()->toArray();
        $page = PaginationHelper::show($list['total'], $list['current_page'], $list['per_page']);

        return view('member.mergeLog.bind-tel', [
            'search' => $search,
            'list' => $list,
            'page' => $page,
        ]);
    }

    public function clickMerge()
    {
        $search = request()->search;
        $list = MemberMergeLog::searchLog($search)->orderBy('created_at', 'desc')->paginate()->toArray();
        $page = PaginationHelper::show($list['total'], $list['current_page'], $list['per_page']);

        return view('member.mergeLog.click-merge', [
            'search' => $search,
            'list' => $list,
            'page' => $page,
        ]);
    }
}