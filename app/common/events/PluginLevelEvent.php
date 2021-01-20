<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/12/6
 * Time: 9:50
 */

namespace app\common\events;

class PluginLevelEvent extends Event
{


    /**
     * SmsMessage constructor.
     * @param array $params
     *
     */
    private $params = [];

    function __construct($params)
    {
        $this->params = $params;

    }

    public function getData()
    {
        return $this->params;
    }




}