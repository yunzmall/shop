<?php

namespace app\Jobs;

use app\common\events\goods\GoodsLimitBuyCloseEvent;
use app\common\models\Goods;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LimitBuyEndJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $limitBuy;

    /**
     * LimitBuyEndJob constructor.
     * @param $data
     */
    public function __construct($data = [])
    {
        $this->limitBuy = $data;
    }

    /**
     * @return bool
     */
    public function handle()
    {
        $goods = Goods::find($this->limitBuy['goods_id']);
        if(!$goods){
            return true;
        }

        //执行限时购结束事件
        event(new GoodsLimitBuyCloseEvent($goods));
        return true;
    }


}
