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
}