<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/12/10
 * Time: 16:55
 */

namespace app\common\events\member;

use app\common\events\Event;

class MemberLevelExpireEvent extends Event
{
    protected $memberIds;

    public function __construct($memberIds = [])
    {
        $this->memberIds = $memberIds;
    }

    public function getMemberIds()
    {
        return $this->memberIds;
    }

}