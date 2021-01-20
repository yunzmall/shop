<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/9
 * Time: 17:37
 */

namespace app\common\services\notice\share;


use app\common\models\Notice;

Trait GoodsNoticeData
{
   public $goodsBuy ;

   public function getGoodsBuy($goodsIds)
   {
       $goods = Notice::select()->whereIn('goods_id', $goodsIds)->with(['hasOneMini'=>function($query){
           $query->select('member_id','openid');
       }])->get();
       $this->goodsBuy = empty($goods) ? [] : $goods->toArray();
   }
}