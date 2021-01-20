<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/12/6
 * Time: 9:50
 */

namespace app\common\events\plugin;


use app\common\events\Event;

class  MicroEvent extends Event
{


    /**
     * @var 区域代理-信息
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