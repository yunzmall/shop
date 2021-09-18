<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/18
 * Time: 15:28
 */

namespace app\backend\modules\order\services\type;


class ShopOrder  extends OrderTypeFactory
{
    protected $name = '自营';

    protected $code = 'shop';

    public function isBelongTo()
    {
        if ($this->order->plugin_id == 0) {
            return true;
        }

        return false;
    }

}