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
use app\common\models\finance\PointQueue;

class QueueController extends BaseController
{
    public function index()
    {
        if (request()->ajax()) {
            $search = request()->search;
            $list = PointQueue::getList($search)
                ->orderBy('id', 'desc')
                ->paginate();
            $amount = $list->sum('point_total');
            $shopSet = Setting::get('shop.member');
            $list = $list->toArray();
            foreach ($list['data'] as &$item) {
                $item['member']['uid'] = $item[['member']] ? $item['member']['uid'] : '';
                $item['member']['avatar'] = $item['member']['avatar'] ? tomedia($item['member']['avatar']) : tomedia($shopSet['headimg']);
                $item['member']['nickname'] = $item['member']['nickname'] ?: '未更新';
                $item['order']['id'] = $item['order'] ? $item['order']['id'] : '';
                $item['order']['order_sn'] = $item['order'] ? $item['order']['order_sn'] : '';
            }

            return $this->successJson('ok', [
                'list' => $list,
                'search' => $search,
                'amount' => $amount,
                'tab_list' => PointService::getVueTags(),
            ]);
        }
        return view('point.queue');
    }
}