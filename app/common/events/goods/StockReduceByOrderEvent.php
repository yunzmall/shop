<?php
/**
 * 订单支付后事件
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 上午11:44
 */

namespace app\common\events\goods;

class StockReduceByOrderEvent extends \app\common\events\Event
{

    protected $goods;
    protected $num;

    public function __construct($goods, $num)
    {
        $this->goods = $goods;
        $this->num = $num;
    }


    public function getGoods()
    {
        return $this->goods;
    }

    public function getNum()
    {
        return $this->num;
    }


}