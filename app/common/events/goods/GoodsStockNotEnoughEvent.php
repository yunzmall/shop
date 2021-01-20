<?php
/**
 * 减库存事件
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/12/6
 * Time: 9:50
 */

namespace app\common\events\goods;

use app\common\events\Event;
use app\common\models\Goods;

class GoodsStockNotEnoughEvent extends Event
{
    /**
     * @var
     */
    private $goods; //商品

    private $specs; //规格

    /**
     * GoodsStockNotEnoughEvent constructor.
     * @param $goods
     * @param null $specs
     */
    function __construct($goods,$specs = Null)
    {
        if(empty($goods) && !empty($specs)){
            $goods = Goods::find($specs->goods_id);
        }

        $this->goods = $goods;
        $this->specs = $specs;
    }

    public function getGoods()
    {
        return $this->goods;
    }


    public function getSpecs()
    {
        return $this->specs;
    }


}