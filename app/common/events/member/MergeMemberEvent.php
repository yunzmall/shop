<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022-05-16
 * Time: 15:36
 */

namespace app\common\events\member;


use app\common\events\Event;

class MergeMemberEvent extends Event
{
    protected $hold_uid;
    protected $give_up_uid;

    public function __construct($hold_uid, $give_up_uid)
    {
        $this->hold_uid = $hold_uid;
        $this->give_up_uid = $give_up_uid;
    }

    public function getHoldUid()
    {
        return $this->hold_uid;
    }

    public function getGiveUpUid()
    {
        return $this->give_up_uid;
    }
}