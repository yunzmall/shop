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
use app\common\services\ExportService;

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
    
    public function export()
    {
	    $search = request()->search;
	    if($search['time_search']) {
		    $search['time']['start'] = $search['time_start'];
		    $search['time']['end'] = $search['time_end'];
	    }
	    $builder = ShoppingShareCouponLog::getList($search)->orderBy('id', 'desc');
	    
	    $export_page = request()->export_page ? request()->export_page : 1;
	    $export_model = new ExportService($builder, $export_page);
	    $file_name = date('Ymdhis', time()) . '优惠券领取发放记录导出';
	    $export_data[0] = ['ID', '优惠券名称', '分享者', '领取者', '创建时间', '日志详情'];
	    if ($export_model->builder_model->isEmpty()) {
		    return $this->message('导出数据为空', Url::absoluteWeb('coupon.share-coupon.log'), 'error');
	    }
	
	    foreach ($export_model->builder_model as $key => $item) {
		    $export_data[$key + 1] = [
			    $item->id,
			    $item->coupon_name,
			    '昵称:'.$item['shareMember']['nickname'] .'/ 电话:'.$item['shareMember']['mobile'],
			    '昵称:'.$item['receiveMember']['nickname'] .'/ 电话:'.$item['receiveMember']['mobile'],
			    date('Y-m-d H:i:s',strtotime($item->created_at)),
			    $item->log,
		    ];
	    }
	    $export_model->export($file_name, $export_data, \Request::query('route'));
    }
}