<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/5/5
 * Time: 11:40
 */

namespace app\common\events\cart;

use app\common\events\Event;

/**
 * 保存购物车之前验证事件
 */
class BeforeSaveCartVerify extends Event
{
    public $goods_id;
    public $total;
    public $type;

    public function __construct($goods_id, $total, $type)
    {
        $this->goods_id = $goods_id;
        $this->total = $total;
        $this->type = $type;
    }

}