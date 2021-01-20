<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/12/6
 * Time: 9:50
 */

namespace app\common\events\plugin;


use app\common\events\Event;

class HotelEvent extends Event
{


    /**
     * @var 酒店-信息
     */

    /**
     * SmsMessage constructor
     * @param array $params
     *
     */
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