<?php

namespace app\Jobs;

use app\common\facades\Setting;
use app\common\models\Goods;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetGoodsPriceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    public $goodsIds;
    public $uniacid;

    public function __construct($goodsIds,$uniacid)
    {
        $this->goodsIds = $goodsIds;
        $this->uniacid = $uniacid;
    }

    public function handle()
    {
        if (!$this->goodsIds) {
            return;
        }
        Setting::$uniqueAccountId = $this->uniacid;
        \YunShop::app()->uniacid = $this->uniacid;
        $goods = Goods::uniacid()->whereIn('id',$this->goodsIds)
            ->with(['hasManyOptions' => function ($options) {
                $options->select('id','goods_id','product_price');
            }])
            ->get();
        $goods->map(function (Goods $good) {
            if ($good->has_option && !$good->hasManyOptions->isEmpty()) {//开启规格
                $good->min_price = $good->hasManyOptions->min('product_price');
                $good->max_price = $good->hasManyOptions->max('product_price');
            } else {
                $good->min_price = $good->price;
                $good->max_price = $good->price;
            }
            $good->save();
        });
    }
}