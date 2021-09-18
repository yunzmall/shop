<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/12/6
 * Time: 9:50
 */

namespace app\common\events;


class StoreSearchEvent extends Event
{

    /**
     * @var 更新门店
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