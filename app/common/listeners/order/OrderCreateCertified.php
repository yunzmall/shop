<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2018/10/6
 * Time: 11:18
 */

namespace app\common\listeners\order;

use app\common\events\order\AfterOrderCreatedEvent;
use app\common\models\Member;
use app\common\models\MemberCertified;
use app\common\services\DivFromService;

class OrderCreateCertified
{
    public function handle(AfterOrderCreatedEvent $event)
    {
        $model = $event->getOrderModel();

        foreach ($model->hasManyOrderGoods as $orderGoods) {
            $goods_ids[] = $orderGoods->goods_id;
        }

        $obj = new \app\common\services\DivFromService();
        if(!$obj->isDisplay($goods_ids)){
            return;
        }

        //查询最新一条记录为当前实名信息
        $info = MemberCertified::getFirstData($model->uid);
        if(!$info){
            $info = Member::select('realname','idcard')->where('uid',$model->uid)->first();
        }

        \Log::info('关联实名认证表订单id',$info);

        if($info['order_id'] == 0 && isset($info['order_id'])){
            //更新实现认证表订单id
            MemberCertified::updateOrderId($info['id'],$model->id,'提交订单');
        }else{
            $data = [
                'realname' => $info->realname,
                'idcard' => $info->idcard,
                'remark' => '提交订单',
                'uniacid' => \YunShop::app()->uniacid,
                'member_id' => $model->uid,
                'order_id' => $model->id,
                'created_at' => time(),
                'updated_at' => time()
            ];
            MemberCertified::insertData($data);
        }

    }

}