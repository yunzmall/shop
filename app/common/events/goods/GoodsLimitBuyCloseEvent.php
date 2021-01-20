<?php
/**
 * 限时购商品下架
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/12/6
 * Time: 9:50
 */

namespace app\common\events\goods;

use app\common\events\Event;

class GoodsLimitBuyCloseEvent extends Event
{
    /**
     * @var
     */
    private $goods; //商品

    /**
     * GoodsLimitBuyCloseEvent constructor.
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