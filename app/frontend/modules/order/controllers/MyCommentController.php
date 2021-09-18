<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/4/20
 * Time: 下午6:05
 */

namespace app\frontend\modules\order\controllers;


use app\common\components\ApiController;
use app\frontend\models\Order;
use app\frontend\models\OrderGoods;

class MyCommentController extends ApiController
{
    public function index()
    {
        $list = Order::getMyCommentList( \YunShop::request()->status)->toArray();
        if(!is_null($event_arr = \app\common\modules\shop\ShopConfig::current()->get('form_comment_list'))){
            foreach ($event_arr as $v){
                $class    = array_get($v, 'class');
                $function = array_get($v, 'function');
                if ($res = $class::$function(['data'=>$list])){
                    $list = $res['data'];
                }
            }
        }
        return $this->successJson('成功', [
            'list' => $list
        ]);
    }

    public function paging()
    {
        $page = \YunShop::request()->page?:1;
//        $page = ($page - 1) ? ($page - 1) *15 : 1;
        $list = Order::getMyCommentListPaginate( \YunShop::request()->status,$page,15)->toArray();

        if(!is_null($event_arr = \app\common\modules\shop\ShopConfig::current()->get('form_comment_list'))){
            foreach ($event_arr as $v){
                $class    = array_get($v, 'class');
                $function = array_get($v, 'function');
                $list = $class::$function($list) ? : $list;
            }
        }
        return $this->successJson('成功', [
            'list' => $list
        ]);
    }

    public function goods()
    {
        $list = OrderGoods::getMyCommentList(1);
        return $this->successJson('成功', [
            'list' => $list->toArray()
        ]);
    }
}