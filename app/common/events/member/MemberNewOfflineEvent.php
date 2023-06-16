<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021/4/27
 * Time: 15:32
 */

namespace app\common\events\member;


use app\common\events\Event;

class MemberNewOfflineEvent extends Event
{
    protected $uid;
    protected $parent_id;
    protected $is_invite;

    public function __construct($uid, $parent_id, $is_invite = true)
    {
        $this->uid       = $uid;
        $this->parent_id = $parent_id;
        $this->is_invite = $is_invite;//触发事件前未锁定 false 已锁定 true
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getParentId()
    {
        return $this->parent_id;
    }

    public function getIsInvite()
    {
        return $this->is_invite;
    }
}