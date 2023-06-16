<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/5/5
 * Time: 17:21
 */

namespace app\common\events\cart;

use app\common\events\Event;

/**
 * 保存购物车之前验证事件
 */
class BeforeCartListVerify extends Event
{
    protected $memberCarts;

    public function __construct($memberCarts)
    {
        $this->memberCarts = $memberCarts;
    }

    public function getMemberCarts()
    {
        return $this->memberCarts;
    }

}