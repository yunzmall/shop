<?php

namespace app\backend\modules\goods\listeners;

use app\common\models\Goods;
use app\common\models\UniAccount;
use Illuminate\Contracts\Events\Dispatcher;
use app\common\models\goods\GoodsService;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
* 
*/
class GoodsServiceListener
{
    use DispatchesJobs;
	
	public function subscribe(Dispatcher $events)
	{
		$events->listen('cron.collectJobs', function () {
		    \Cron::add('upperLowerShelves', '*/10 * * * *', function() {
		        $this->handle();
		    });
		});
	}

	public function handle()
	{
        $uniAccount = UniAccount::getEnable() ?: [];
        foreach ($uniAccount as $u) {
            \YunShop::app()->uniacid = $u->uniacid;
            \Setting::$uniqueAccountId = $u->uniacid;

            \Log::debug('-----------商品定时上下架-----------------uniacid:'.\YunShop::app()->uniacid);

            $goods = Goods::select(['id', 'status'])
                ->whereHas('hasOneGoodsService', function ($query) {
                    return $query->where('is_automatic', 1);
                })->with(['hasOneGoodsService' => function ($query2) {
                    return $query2->select(['goods_id', 'on_shelf_time', 'lower_shelf_time']);
                }])->get();

            $limit_goods = Goods::select(['id', 'status'])
                ->whereHas('hasOneGoodsLimitBuy', function ($query) {
                    return $query->where('status', 1);
                })->with(['hasOneGoodsLimitBuy' => function ($query2) {
                    return $query2->select(['goods_id', 'start_time', 'end_time', 'status']);
                }])->get();

            if ($limit_goods) {
                foreach ($limit_goods as $item) {
                    if ($item->hasOneGoodsLimitBuy->status == 1 && $item->hasOneGoodsLimitBuy->end_time < time()) {
                        if ($item->status == 1) {
                            $item->hasOneGoodsLimitBuy->status = 0;
                            $item->status = 0;
                            $item->save();
                        }
                    }
                }
            }

            if ($goods) {
                $current_time = time();
                foreach ($goods as $key => $item) {
                    //上架
                    if ($item->hasOneGoodsService->on_shelf_time < $current_time) {
                        if ($item->status == 0) {
                            $item->status = 1;
                            $item->save();
                        }
                    }

                    //下架
                    if ($item->hasOneGoodsService->lower_shelf_time < $current_time) {
                        if ($item->status == 1) {
                            $item->status = 0;
                            $item->save();
                        }
                    }
                }
            }
        }
	}
}