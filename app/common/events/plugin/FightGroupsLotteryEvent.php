<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/6/8
 * Time: 10:27
 */

namespace app\common\events\plugin;

use app\common\events\Event;

class FightGroupsLotteryEvent extends Event
{


    /**
     * @var 分销商-信息
     */

    /**
     * SmsMessage constructor.
     * @param array $params
     *
     */
    private $data ;

    function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }



}
