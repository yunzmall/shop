<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/4
 * Time: 上午9:09
 */

namespace app\backend\modules\order\controllers;

use app\backend\modules\order\models\Order;
use app\backend\modules\order\models\OrderGoods;
use app\backend\modules\goods\models\Goods;


use app\backend\modules\order\services\OrderPackageService;
use app\common\components\BaseController;
use app\common\models\order\OrderPackage;

class MultiplePackagesOrderGoodsController extends BaseController
{
    /**
     * 获取可选择的商品 用户多包裹发货
     */
   public function getOrderGoods()
   {
       $order_id = intval(request()->input('order_id'));

       //查询这个订单没有发货的商品
       $where[] = ['order_id','=',$order_id];
//       $where[] = ['order_express_id','=',null];
       $select = ['id','goods_id','order_id','thumb','title','goods_option_title','goods_sn','goods_market_price','payment_amount','total'];
       $order_goods = OrderGoods::uniacid()->where($where)->whereNull('order_express_id')->select($select)->get()->makeVisible('order_id');
       $order_package = OrderPackage::uniacid()->where($where)->whereNotNull('order_express_id')->get();
       $order_goods = OrderPackageService::filterGoods($order_goods,$order_package);
       $order_goods = array_values($order_goods->toArray());
       return $this->successJson('操作成功',$order_goods);
   }

    /**
     * 用来修复多包裹发货默认值为1(部分发货) 之后批量发货造成默认值不会更改 造成用户无法收货的问题
     */
   public function repairMultiplePackages()
   {

       $where[] = ['status','!=',2];
       $where[] = ['is_all_send_goods','=',1];
       Order::where($where)->update(['is_all_send_goods'=>0]); //修改所有状态不等于发货的状态为0
       $where = [];
       $where[] = ['status','=',2];
       $where[] = ['is_all_send_goods','=',1];
       $data = Order::where($where)->with(['hasManyOrderGoods'])->get();

       foreach ($data as $k=>$v){
           $order_data = $v->toArray();
           if(count($order_data['has_many_order_goods'])==1){
               $v->is_all_send_goods = 0;
               $v->save();
           }else{
               $order_goods_express_id_is_null = 0; //计数 如果订单商品所有该字段都是空 则状态变为全部发货
               $order_goods_express_id_num = 0;//计数 如果订单商品所有该字段都不为空则变为全部发货
               foreach ($order_data['has_many_order_goods'] as $k1=>$v1){
                   if(empty($v1['order_express_id'])){
                       $order_goods_express_id_is_null++;
                   }else{
                       $order_goods_express_id_num++;
                   }
               }
               if($order_goods_express_id_is_null == count($order_data['has_many_order_goods'])){
                   $v->is_all_send_goods = 0;
                   $v->save();
               }
               if($order_goods_express_id_num == count($order_data['has_many_order_goods'])){
                   $v->is_all_send_goods = 0;
                   $v->save();
               }
           }
       }
       return $this->successJson();
   }
}