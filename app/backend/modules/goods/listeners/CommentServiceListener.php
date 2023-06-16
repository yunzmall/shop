<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/1/13
 * Time: 11:16
 */

namespace app\backend\modules\goods\listeners;

use app\backend\modules\order\models\OrderGoods;
use app\common\models\Comment;
use app\common\models\comment\CommentConfig;
use app\common\models\Order;
use app\common\models\UniAccount;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommentServiceListener
{
    use DispatchesJobs;

    public function subscribe(Dispatcher $events)
    {
        $events->listen('cron.collectJobs', function () {
            //该功能对时效性要求不高，选个任务少的时间段执行即可
            \Cron::add('goodsDefaultFavorableComment', '*/30 3 * * *', function () {
                $this->handle();
            });
        });
    }

    public function handle()
    {
        $storePlugin = [31,32];
        $waitStatusComplete = 0;//未评价
        $fifteenDayTime = 24 * 60 * 60 * 15;//15天时间戳
        $defaultTime = Carbon::today()->timestamp;//当天0时
        $nowTime = time();
        $dbPrefix = app('db')->getTablePrefix();

        $uniAccount = UniAccount::getEnable() ?: [];
        foreach ($uniAccount as $u) {

            \YunShop::app()->uniacid = $u->uniacid;
            \Setting::$uniqueAccountId = $u->uniacid;

            $is_default_good_reputation = CommentConfig::getSetConfig('is_default_good_reputation');
            if (!$is_default_good_reputation) {
                continue;
            }

            \Log::debug('-----------商品自动好评-----------------uniacid:' . \YunShop::app()->uniacid);

            $orderGoodsModel = OrderGoods::uniacid()
                ->select([
                    'yz_order_goods.id', 'yz_order_goods.uid', 'yz_order_goods.order_id', 'yz_order_goods.uniacid', 'yz_order_goods.goods_id', 'yz_order.plugin_id', 'yz_order.finish_time'
                ])
                ->leftJoin('yz_order', 'yz_order_goods.order_id', 'yz_order.id')
                ->where('yz_order.status', Order::COMPLETE)
                ->whereRaw('(' . $dbPrefix . 'yz_order.finish_time + ?) <= ?', [$fifteenDayTime, $defaultTime])
                ->where('yz_order_goods.comment_status',$waitStatusComplete)
                ->with([
                    'belongsToMember' => function ($query) {
                        $query->select('uid', 'nickname', 'avatar');
                    }
                ])
                ->limit(10000);//限制下条数,避免长时间未评价的订单全部查询导致跑不过来

            $storeIdsRelation = [];
            if (app('plugins')->isEnabled('store-cashier')) {
                $orderIds = $orderGoodsModel->pluck('order_id');
                if ($orderIds->isNotEmpty()) {
                    foreach (array_chunk($orderIds->toArray(),1000) as $ids) {
                        $storeIdsRelation += \Yunshop\StoreCashier\common\models\StoreOrder::select('order_id', 'store_id')->whereIn('order_id', $ids)->pluck('store_id', 'order_id')->toArray();
                    }
                }
            }

            $orderGoodsData = $orderGoodsModel->get();
            $commentData = $yzOrderGoodsIds = [];
            foreach ($orderGoodsData as $item) {
                if (in_array($item->plugin_id,$storePlugin) && !empty($storeIdsRelation[$item->order_id])) {
                    $plugin_table_id = $storeIdsRelation[$item->order_id];
                }

                $commentData = [
                    'uniacid' => $item->uniacid,
                    'order_id' => $item->order_id,
                    'goods_id' => $item->goods_id,
                    'uid' => $item->uid,
                    'nick_name' => $item->belongsToMember->nickname,
                    'head_img_url' => $item->belongsToMember->avatar,
                    'content' => '',
                    'level' => 5,//5星好评
                    'type' => 1,
                    'plugin_id' => $item->plugin_id,
                    'plugin_table_id' => $plugin_table_id,
                    'has_default_good_reputation' => 1,//默认好评
                    'created_at' => $nowTime,
                    'updated_at' => $nowTime,
                ];

                //todo 后续优化插入 更新
                $comment_id = Comment::insertGetId($commentData);

                OrderGoods::where('id',$item->id)->update([
                    'comment_status' => 1,
                    'comment_id' => $comment_id
                ]);

//                $yzOrderGoodsIds[] = $item->id;
            }
//            \Log::info('商品自动好评',$yzOrderGoodsIds);

//            Comment::insert($commentData);


//            try {
//                DB::transaction(function () use ($commentData,$yzOrderGoodsIds) {
//                    OrderGoods::whereIn('id',$yzOrderGoodsIds)->update([
//                        'comment_status' => 1,
//                        'comment_id' => 0
//                    ]);
//                    \Log::info('商品自动好评',$yzOrderGoodsIds);
//                });
//            } catch (\Exception $exception) {
//                \Log::error('商品自动评价error',$exception->getMessage());
//            }

        }
    }
}