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
use app\common\models\member\MemberMerge;
use app\common\models\member\MemberMergeLog;
use app\common\models\SynchronizedBinder;
use Yunshop\Love\Common\Services\SetService;

class MergeLogController extends BaseController
{
    public function index()
    {
        $search = request()->search;
        if ($search) {
            $list = MemberMerge::search($search)->orderBy('id', 'desc')->paginate();
            return $this->successJson('ok', [
                'list' => $list,
            ]);
        }
        $love_name = '';
        if (app('plugins')->isEnabled('love')) {
            $love_name = SetService::getLoveName();
        }
        $list = MemberMerge::uniacid()->orderBy('id', 'desc')->paginate();
        return view('member.mergeLog.auth-merge', [
            'list' => $list,
            'love_name' => $love_name,
        ])->render();
    }

    public function oldLog()
    {
        return view('member.mergeLog.old-log', [])->render();
    }

    public function authMerge()
    {
        $search = request()->search;
        $list = MemberChangeLog::searchLog($search)->orderBy('created_at', 'desc')->paginate()->toArray();
        return $this->successJson('ok', [
            'search' => $search,
            'list' => $list,
        ]);
    }

    public function bindTel()
    {
        $search = request()->search;
        $list = SynchronizedBinder::searchLog($search)->orderBy('created_at', 'desc')->paginate()->toArray();
        return $this->successJson('ok', [
            'search' => $search,
            'list' => $list,
        ]);
    }

    public function clickMerge()
    {
        $search = request()->search;
        $list = MemberMergeLog::searchLog($search)->orderBy('created_at', 'desc')->paginate()->toArray();
        return $this->successJson('ok', [
            'search' => $search,
            'list' => $list,
        ]);
    }

}