<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/1/2
 * Time: 18:51
 */

namespace app\backend\modules\charts\modules\goods\controllers;


use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\models\Goods;
use Illuminate\Support\Facades\DB;

class SalesVolumeCountController extends BaseController
{
    public function index()
    {
          $list = DB::table('yz_order_goods')
            ->join('yz_order',function ($join){
                $join->on('yz_order_goods.order_id', '=', 'yz_order.id');
            } )
            ->rightJoin('yz_goods',function ($join){
                $join->on('yz_order_goods.goods_id', '=', 'yz_goods.id');
            } )
              ->where('yz_order_goods.uniacid',\YunShop::app()->uniacid)
           ->where('yz_goods.plugin_id',0)
            ->where('yz_goods.is_plugin',0)
            ->where('yz_goods.status',1)
            ->where('yz_order.status',3)
             ->select(DB::raw('ims_yz_order_goods.order_id,ims_yz_order_goods.goods_id,SUM(ims_yz_order_goods.total) as total_sum,ims_yz_order.id as order_real_id,ims_yz_order.status as order_status,ims_yz_goods.id as goods_real_id,ims_yz_goods.title,ims_yz_goods.status,ims_yz_goods.is_plugin,ims_yz_goods.plugin_id'))
            ->groupBy('yz_order_goods.goods_id')
            ->havingRaw('SUM(ims_yz_order_goods.total) != 0')
            ->orderBy('total_sum', 'desc')
            ->paginate(20);

        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());;

        //处理排名问题,之前没有做分页直接拿健做排名,做了分页不能用健做排名了
        $list = $list->toArray();
        $count =  $list['from'];
        foreach ($list['data'] as $k => &$item){
            $item['ranking'] = $count + $k;
        }
        return view('charts.goods.sales_volume_count',[
            'list' => $list['data'],
            'pager' => $pager,
        ])->render();
    }

    public function salesPrice()
    {
        $list = DB::table('yz_order_goods')
            ->join('yz_order',function ($join){
                $join->on('yz_order_goods.order_id', '=', 'yz_order.id');
            } )
            ->rightJoin('yz_goods',function ($join){
                $join->on('yz_order_goods.goods_id', '=', 'yz_goods.id');
            } )
            ->where('yz_order_goods.uniacid',\YunShop::app()->uniacid)
            ->where('yz_goods.plugin_id',0)
            ->where('yz_goods.is_plugin',0)
            ->where('yz_goods.status',1)
            ->where('yz_order.status',3)
            ->select(DB::raw('ims_yz_order_goods.order_id,ims_yz_order_goods.goods_id,SUM(ims_yz_order_goods.price) as price_sum,ims_yz_order.id as order_real_id,ims_yz_order.status as order_status,ims_yz_goods.id as goods_real_id,ims_yz_goods.title,ims_yz_goods.status,ims_yz_goods.is_plugin,ims_yz_goods.plugin_id'))
            ->groupBy('yz_order_goods.goods_id')
            ->havingRaw('SUM(ims_yz_order_goods.price) != 0')
            ->orderBy('price_sum', 'desc')
            ->paginate(20);

        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());;

        //处理排名问题,之前没有做分页直接拿健做排名,做了分页不能用健做排名了
        $list = $list->toArray();
        $count =  $list['from'];
        foreach ($list['data'] as $k => &$item){
            $item['ranking'] = $count + $k;
        }

        return view('charts.goods.sales_count',[
            'list' => $list['data'],
            'pager' => $pager,
        ])->render();
    }

}