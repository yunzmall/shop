<?php

namespace app\common\events\goods;

use app\common\events\Event;

class GoodsCreateEvent extends Event
{
    /**
     * @var
     */
    private $goods; //商品

    /**
     * @param $goods
     */
    function __construct($goods)
    {
        $this->goods = $goods;
    }

    public function getGoods()
    {
        return $this->goods;
    }
}