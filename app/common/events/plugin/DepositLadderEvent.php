<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021-05-13
 * Time: 14:52
 */

namespace app\common\events\plugin;


use app\common\events\Event;

class DepositLadderEvent extends Event
{
    private $data = [];

    /**
     * 定金阶梯团事件
     * SnatchRegimentEvent constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}