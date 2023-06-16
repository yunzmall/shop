<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022/5/5
 * Time: 16:20
 */

namespace app\common\events\goods;

use app\common\events\Event;

/**
 * 保存购物车之前验证事件
 */
class BeforeSaveGoodsVerify extends Event
{
    public $goods_id;
    public $total;

    public function __construct($goods_id, $total)
    {
        $this->goods_id = $goods_id;
        $this->total = $total;
    }
}