<?php
/**
 * 订单支付后事件
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/3
 * Time: 上午11:44
 */

namespace app\common\events\goods;

class ActualStockForOrderEvent extends \app\common\events\Event
{

    protected $goods;
    protected $is_replace;
    protected $actual_stock;

    public function __construct($goods)
    {
        $this->goods = $goods;
        $this->actual_stock = 0;
        $this->is_replace = 0;
    }


    public function getGoods()
    {
        return $this->goods;
    }

    public function setActualStock($stock, $msg = '')
    {
        $this->actual_stock = $stock;
        $this->is_replace = 1;
        \Log::debug('插件设置当前实际库存:' . $msg, $stock);
    }

    public function getReplaceStock()
    {
        if ($this->is_replace) {
            return $this->actual_stock;
        } else {
            return false;
        }
    }


}