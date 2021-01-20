<?php


namespace app\frontend\modules\orderGoods\stock\types;


use app\common\models\Goods;
use app\common\models\GoodsOption;
use app\common\models\OrderGoods;
use app\frontend\modules\orderGoods\stock\GoodsStock;
use Illuminate\Database\Eloquent\Model;

abstract class StockType
{
    /**
     * @var Goods|GoodsOption
     */
    private $source;
    /**
     * @var OrderGoods
     */
    protected $orderGoods;
    /**
     * @var GoodsStock
     */
    protected $goodsStock;

    public function __construct(GoodsStock $goodsStock, Model $source)
    {
        $this->source = $source;
        $this->goodsStock = $goodsStock;
        $this->orderGoods = $goodsStock->orderGoods();
    }

    /**
     * @return Goods|GoodsOption
     */
    protected function source()
    {
        return $this->source;
    }

    /**
     * @return OrderGoods
     */
    protected function orderGoods()
    {
        return $this->orderGoods;
    }

    /**
     * @return bool
     */
    abstract public function rollback();

    /**
     * @return bool
     */
    abstract function reduce();

    /**
     * @return bool
     */
    abstract public function withhold();

    /**
     * @return bool
     */
    abstract public function shouldWithhold();
    abstract public function withholdRecord();


}