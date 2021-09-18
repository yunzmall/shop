<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/18
 * Time: 15:20
 */

namespace app\backend\modules\order\services\type;


class NoneOrder extends OrderTypeFactory
{
    protected $name = '未知类型';

    protected $code = 'none';

    public function isBelongTo()
    {
        return true;
    }

    public function buttonModels()
    {
        return [];
    }

    public function fixedButton()
    {
        $array = parent::fixedButton();

        $array = array_map(function ($item) {
            $item['is_show'] = false;
            return $item;
        }, $array);

        return $array;
    }


    public function topShow()
    {
        return $this->order->plugin_id;
    }

}