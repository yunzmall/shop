<?php


namespace app\frontend\modules\goods\stock;


use app\common\events\goods\ActualStockForOrderEvent;
use app\common\events\goods\StockReduceByOrderEvent;
use app\common\models\Goods;
use app\common\models\GoodsOption;
use Illuminate\Support\Facades\Redis;

class GoodsStock
{
    /**
     * @var Goods|GoodsOption
     */
    private $source;

    public function __construct($source)
    {
        $this->source = $source;
    }

    public function withhold($num)
    {
        return Redis::incrby($this->withholdStockKey(), $num);
    }

    public function rollback($num)
    {
        if (Redis::get($this->withholdStockKey()) - $num < 0) {
            return false;
        }
        return Redis::decrby($this->withholdStockKey(), $num);
    }

    public function reduce($num)
    {
        try {
            \Log::debug('商品扣库存', "商品(" . get_class($this->source()) . ":{$this->source()->id}-{$this->source()->stock})减库存{$num}件");
        }catch (\Exception $e){

        }
        if (($this->source()->stock - $num) <= 0) {
            $this->source()->fireStockNotEnoughtEvent($this->source());
        }
        // 数据库减库存
        event(new StockReduceByOrderEvent($this->source(),$num));
        return $this->source()->decrement('stock', $num);
    }

    public function createReduce($num)
    {
        try {
            \Log::debug('商品扣库存', "商品(" . get_class($this->source()) . ":{$this->source()->id}-{$this->source()->stock})减库存{$num}件");
        }catch (\Exception $e){

        }
        if (($this->source()->stock - $num) <= 0) {
            $this->source()->fireStockNotEnoughtEvent($this->source());
        }
        // 数据库减库存
        event(new StockReduceByOrderEvent($this->source(),$num));
        return $this->source()->where('id',$this->source()->id)->where('stock','>=',$num)->decrement('stock', $num);
    }

    public function enough($num)
    {
        return $this->usableStock() >= $num;
    }

    public function usableStock()
    {
        event($event = new ActualStockForOrderEvent($this->source()));
        $stock = $event->getReplaceStock() === false ? $this->stock() : $event->getReplaceStock();
        return $stock - $this->withholdStock();
    }

    public function withholdStock()
    {
        return Redis::get($this->withholdStockKey()) ?: 0;
    }

    public function stock()
    {
        return $this->source()->getOriginal('stock');
    }

    /**
     * @return Goods|GoodsOption
     */
    private function source()
    {
        return $this->source;
    }

    private function withholdStockKey()
    {
        return $this->source()->getTable() . ":{$this->source()->id}:withhold_stock";
    }

}