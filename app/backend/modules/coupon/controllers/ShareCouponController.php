<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 10:17
 */

namespace app\backend\modules\coupon\controllers;

use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\common\models\coupon\ShoppingShareCouponLog;

class ShareCouponController extends BaseController
{
    public function log()
    {
        return view('coupon.share-log')->render();
//        $search = request()->search;
//        $list = ShoppingShareCouponLog::getList($search)->orderBy('id', 'desc')->paginate(15);
//        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());
//
//        if(!$search['time']) {
//
//            $search['time']['start'] = date("Y-m-d H:i:s",time());
//            $search['time']['end'] = date("Y-m-d H:i:s",time());
//        }
//
//        $data = [
//            'list' => $list->toArray(),
//            'pager' => $pager,
//            'search' => $search,
//        ];
//
//        return $this->successJson('ok',$data);

//        return view('coupon.share-log', [
//            'list' => $list->toArray(),
//            'pager' => $pager,
//            'search' => $search,
//        ])->render();
    }

    public function shareLogData()
    {
        $search = request()->search;
        $list = ShoppingShareCouponLog::getList($search)->orderBy('id', 'desc')->paginate(15);
        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());

        if(!$search['time']) {

            $search['time']['start'] = date("Y-m-d H:i:s",time());
            $search['time']['end'] = date("Y-m-d H:i:s",time());
        }

        $data = [
            'list' => $list->toArray(),
            'search' => $search,
        ];

        return $this->successJson('ok',$data);
    }
}