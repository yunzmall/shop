<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021-05-12
 * Time: 13:45
 */

namespace app\common\events\plugin;


use app\common\events\Event;

class SnatchRegimentEvent extends Event
{
    private $data = [];

    /**
     * 抢团事件
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