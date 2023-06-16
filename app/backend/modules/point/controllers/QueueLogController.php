<?php
/**
 * Author:
 * Date: 2019/3/31
 * Time: 9:42 PM
 */

namespace app\backend\modules\point\controllers;


use app\backend\modules\finance\services\PointService;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use app\common\models\finance\PointQueueLog;
use app\common\models\point\ParentRewardLog;

class QueueLogController extends BaseController
{
    public function index()
    {
        if (request()->ajax()) {
            $search = request()->search;
            $list = PointQueueLog::getList($search);
            $amount = $list->sum('amount');
            $shopSet = Setting::get('shop.member');
            $list = $list->orderBy('id', 'desc')->paginate()->toArray();
            foreach ($list['data'] as &$item) {
                $item['member']['uid'] = $item[['member']] ? $item['member']['uid'] : '';
                $item['member']['avatar'] = $item['member']['avatar'] ? tomedia($item['member']['avatar']) : tomedia($shopSet['headimg']);
                $item['member']['nickname'] = $item['member']['nickname'] ?: '未更新';
            }
            return $this->successJson('ok', [
                'list' => $list,
                'tab_list' => PointService::getVueTags(),
                'search' => $search,
                'amount' => $amount,
            ]);
        }
        return view('point.queueLog');
    }

    public function parentIndex()
    {
        if (request()->ajax()) {
            $search = request()->search;

            $query = ParentRewardLog::uniacid()->where('expect_reward_time', '<>', 0)
                ->with([
                    'hasOneOrder' => function ($query) {
                        $query->select('yz_order.id', 'yz_order.order_sn', 'yz_order.uid', 'mc_members.nickname', 'mc_members.avatar')
                            ->leftJoin('mc_members', 'mc_members.uid', '=', 'yz_order.uid');
                    },
                    'hasOneMember' => function ($query) {
                        $query->select('uid', 'nickname', 'avatar');
                    },
                ])->orderBy('id', 'DESC');

            if ($search['status'] || $search['status'] === '0' || $search['status'] === 0) {
                $query->where('status', $search['status']);
            }

            if ($search['order_sn']) {
                $query->whereHas('hasOneOrder', function ($query) use ($search) {
                    $query->where('order_sn', 'like', "%{$search['order_sn']}%");
                });
            }

            if ($search['uid']) {
                $query->where('uid', $search['uid']);
            }

            if ($search['member_kwd']) {
                $query->whereHas('hasOneMember', function ($query) use ($search) {
                    $query->where('nickname', 'like', "%{$search['member_kwd']}%")
                        ->orWhere('mobile', 'like', "%{$search['member_kwd']}%")
                        ->orWhere('realname', 'like', "%{$search['member_kwd']}%");
                });
            }
            $amount = $query->sum('point');
            $list = $query->paginate()->toArray();
            $shopSet = Setting::get('shop.member');
            foreach ($list['data'] as &$item) {
                $item['has_one_member']['uid'] = $item[['has_one_member']] ? $item['member']['uid'] : '';
                $item['has_one_member']['avatar'] = $item['has_one_member']['avatar'] ? tomedia($item['has_one_member']['avatar']) : tomedia($shopSet['headimg']);
                $item['has_one_member']['nickname'] = $item['has_one_member']['nickname'] ?: '未更新';
                $item['has_one_order']['id'] = $item['has_one_order'] ? $item['has_one_order']['id'] : '';
                $item['has_one_order']['order_sn'] = $item['has_one_order'] ? $item['has_one_order']['order_sn'] : '';
            }
            return $this->successJson('ok', [
                'list' => $list,
                'search' => $search,
                'tab_list' => PointService::getVueTags(),
                'amount' => $amount
            ]);
        }

        return view('point.parentQueueLog');
    }
}