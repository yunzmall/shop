<?php
/**
 * Created by PhpStorm.
 * User: 17812
 * Date: 2020/8/31
 * Time: 11:22
 */

namespace app\backend\modules\coupon\controllers;


use app\common\components\BaseController;
use app\common\models\coupon\CouponUseLog;
use app\common\helpers\PaginationHelper;
use app\common\services\ExportService;

class CouponUseController extends BaseController
{
    private $page;


    public function index()
    {
        $this->page = $this->getPage();
        $search = request()->search;
        $list = CouponUseLog::getRecords($search)->paginate('', ['*'], '', $this->page);
        $list = $list->toArray();
        return view('coupon.coupon-use', [
            'list' => json_encode($list),
            'use_type' => json_encode(CouponUseLog::$TypeComment),
        ])->render();
    }

   public function log()
   {
       $this->page = $this->getPage();
       $search = request()->search;
       $list = CouponUseLog::getRecords($search)->paginate('', ['*'], '', $this->page);
       $list = $list->toArray();
       return $this->successJson('ok', ['list' => $list]);
   }

    /**
     * @return int
     */
    private function getPage()
    {
        return  (int)request()->page ?: 1;
    }
	
	
	public function export()
	{
		$search = request()->search;
		if($search['is_time']) {
			$search['time']['start'] = $search['time_start'];
			$search['time']['end'] = $search['time_end'];
		}
		$builder = CouponUseLog::getRecords($search)->orderBy('id', 'desc');
		
		$export_page = request()->export_page ? request()->export_page : 1;
		$export_model = new ExportService($builder, $export_page);
		$file_name = date('Ymdhis', time()) . '优惠券领取发放记录导出';
		$export_data[0] = ['使用时间', '优惠券名称', '会员', '使用类型', '详情'];
		if ($export_model->builder_model->isEmpty()) {
			return $this->message('导出数据为空', Url::absoluteWeb('coupon.coupon-use.index'), 'error');
		}
		
		foreach ($export_model->builder_model as $key => $item) {
			$export_data[$key + 1] = [
				date('Y-m-d H:i:s',strtotime($item->created_at)),
				$item['hasOneCoupon']['name'],
				'昵称:'.$item['belongsToMember']['nickname'] .'/ 电话:'.$item['belongsToMember']['mobile'],
				$item['type_name'],
				$item['detail'],
			];
		}
		$export_model->export($file_name, $export_data, \Request::query('route'));
	}
}