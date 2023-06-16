<?php

namespace app\backend\modules\goods\listeners;

use app\common\models\Goods;
use app\common\models\GoodsOption;
use app\common\models\UniAccount;
use Illuminate\Contracts\Events\Dispatcher;
use app\common\models\goods\GoodsService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Carbon;

/**
* 
*/
class GoodsServiceListener
{
    use DispatchesJobs;
	
	public function subscribe(Dispatcher $events)
	{
		$events->listen('cron.collectJobs', function () {
		    \Cron::add('upperLowerShelves', '*/5 * * * *', function() {
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
            \Log::debug('-----------商品服务提供定时上下架-----------------uniacid:'.\YunShop::app()->uniacid);
            $goods = Goods::select(['id', 'stock','status','updated_at'])
                ->whereHas('hasOneGoodsService', function ($query) {
                    return $query->where('is_automatic', 1);
                })->with(['hasOneGoodsService' => function ($query2) {
                    return $query2->select('*');
                }])->get();
            if ($goods->isEmpty()) {
                continue;
            }
            $current_time = time();
            foreach ($goods as $key => $item) {
                if (!$item->hasOneGoodsService->time_type) {
                    //上架
                    if ($item->hasOneGoodsService->on_shelf_time < $current_time && $item->status == 0) {
                        $item->update(['status'=>1]);
                    }
                    //下架
                    if ($item->hasOneGoodsService->lower_shelf_time < $current_time && $item->status == 1) {
                        $item->update(['status'=>0]);
                    }
                } else {
                    //在循环日期内
                    if (!$item->hasOneGoodsService->loop_time_up || !$item->hasOneGoodsService->loop_time_down) {
                        continue;
                    }
                    if ($item->hasOneGoodsService->loop_date_start < $current_time
                        && $item->hasOneGoodsService->loop_date_end > $current_time) {
                        $down_to_timestamp = strtotime($item->hasOneGoodsService->loop_time_down);
                        $up_to_timestamp = strtotime($item->hasOneGoodsService->loop_time_up);
//                        if ($down_to_timestamp < $up_to_timestamp) {
//                            $down_to_timestamp = Carbon::createFromTimestamp(strtotime($item->hasOneGoodsService->loop_time_down))->addDays(1)->timestamp;
//                        }
                        //上架
                        if ($item->updated_at->timestamp - 120 < $up_to_timestamp
                            && $up_to_timestamp < $current_time
                            && $current_time < $up_to_timestamp + 180
                            && !$item->status) {
                            if ($item->hasOneGoodsService->auth_refresh_stock && $item->hasOneGoodsService->original_stock > 0) {
                                $stock = $item->hasOneGoodsService->original_stock - $item->withhold_stock;
                                if ($stock <= 0) {
                                    $stock = 0;
                                }
                                $item->stock = $stock;
                                $goods_option = GoodsOption::uniacid()->where('goods_id', $item->id)->get();
                                foreach ($goods_option as $value) {
                                    $option_stock = $item->hasOneGoodsService->original_stock - $value->withhold_stock;
                                    if ($option_stock <= 0) {
                                        $option_stock = 0;
                                    }
                                    $value->stock = $option_stock;
                                    $value->save();
                                }
                            }
                            $item->status = 1;
                            $item->save();
                        }
                        //下架
                        if ($item->updated_at->timestamp - 120 < $down_to_timestamp
                            && $down_to_timestamp < $current_time
                            && $current_time < $down_to_timestamp + 180
                            && $item->status) {
                            $item->status = 0;
                            $item->save();
                        }
                    }
                    //过了循环日期后关闭自动上下架功能
                    if ($current_time > $item->hasOneGoodsService->loop_date_end) {
                        $item->hasOneGoodsService->update(['is_automatic'=>0]);
                    }
                }
            }
        }
	}

}