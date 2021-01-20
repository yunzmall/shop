<?php


namespace app\frontend\modules\orderGoods\stock;


use app\common\facades\SiteSetting;
use app\common\models\OrderGoods;
use app\frontend\modules\orderGoods\stock\types\AfterOrderCreate;
use app\frontend\modules\orderGoods\stock\types\Never;
use app\frontend\modules\orderGoods\stock\types\StockType;
use app\frontend\modules\orderGoods\stock\types\AfterOrderPaid;
use Illuminate\Database\Eloquent\Model;

class GoodsStock
{
    /**
     * @var StockType
     */
    private $type;
    /**
     * @var Model
     */
    private $source;
    /**
     * @var OrderGoods
     */
    private $orderGoods;

    public function __construct(OrderGoods $orderGoods)
    {
        $this->orderGoods = $orderGoods;

        if ($this->orderGoods->goods_option_id) {
            $source = $this->orderGoods->goodsOption;
        } else {
            $source = $this->orderGoods->goods;
        }
        if(!$source){
            $type = new Never();
        }else{
            switch ($this->orderGoods->goods->reduce_stock_method) {
                case 0:
                    $type = new AfterOrderCreate($this, $source);
                    break;
                case 1:
                    $type = new AfterOrderPaid($this, $source);
                    break;
                case 2:
                    $type = new Never();
                    break;
                default:
                    $type = new Never();

            }
        }

        $this->type = $type;

        $this->source = $source;
    }

    public function orderGoods()
    {
        return $this->orderGoods;
    }

    private function source()
    {
        return $this->source;
    }
    public function withholdRecord(){
        if (!$this->type->shouldWithhold()) {
            return true;
        }
        // 记录预扣库存的对应记录
        return $this->type->withholdRecord();
    }
    public function withhold()
    {
        if (!$this->type->shouldWithhold()) {
            return true;
        }
        // 记录预扣库存的对应记录
        return $this->type->withhold();
    }

    public function reduce()
    {
        return $this->type->reduce();
    }

    public function rollback()
    {
        return $this->type->rollback();
    }

    public function enough()
    {
        return $this->type->enough();
    }

    public function keyOfWithholdKeySet()
    {
        return "withhold_order_goods_id_keys";

    }

    public function withholdKey()
    {
        return $this->source()->getTable() . ":{$this->source()->id}:withhold_order_goods_id";
    }
}