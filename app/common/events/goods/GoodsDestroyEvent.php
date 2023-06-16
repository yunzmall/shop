<?php

namespace app\common\events\goods;

use app\common\events\Event;

class GoodsDestroyEvent extends Event
{
    protected $goods_id;

    public function __construct($goods_id)
    {
        $this->goods_id = $goods_id;
    }

    public function getGoodsId()
    {
        return $this->goods_id;
    }
}