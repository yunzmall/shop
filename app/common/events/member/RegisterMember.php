<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2020/3/6
 * Time: 下午3:40
 */

namespace app\common\events\member;


use app\common\events\Event;

class RegisterMember extends Event
{
    private $uid = '';
    private $mid = '';

    public function __construct($mid, $uid)
    {
        $this->uid = $uid;
        $this->mid = $mid;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getMid()
    {
        return $this->mid;
    }
}
