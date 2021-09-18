<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/12/6
 * Time: 9:50
 */

namespace app\common\events\plugin;


use app\common\events\Event;

class ChannelEvent extends Event
{
    private $data = [];

    function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}