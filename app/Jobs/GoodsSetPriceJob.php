<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2023/4/19
 * Time: 17:38
 */

namespace app\Jobs;

use app\common\facades\Setting;
use app\common\models\Goods;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GoodsSetPriceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $goods_id;
    protected $uniacid;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uniacid,$goods_id)
    {
        $this->uniacid = $uniacid;
        $this->goods_id = $goods_id;
    }

    /**
     * @return bool|void
     */
    public function handle()
    {
        \YunShop::app()->uniacid = Setting::$uniqueAccountId = $this->uniacid;
        $good = Goods::find($this->goods_id);
        if (!$good) {
            return;
        }
        return $this->setPrice($good);
    }

    private function setPrice(Goods $good)
    {
        if ($good->has_option && !$good->hasManyOptions->isEmpty()) {//开启规格
            $min_price = $good->hasManyOptions->min('product_price');
            $max_price = $good->hasManyOptions->max('product_price');
        } else {
            $min_price = $good->price;
            $max_price = $good->price;
        }
        DB::table('yz_goods')->where('id',$good->id)->update(['min_price'=>$min_price,'max_price'=>$max_price]);//不能用模型去改
        return true;
    }
}