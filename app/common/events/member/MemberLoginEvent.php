<?php
/******************************************************************************************************************
 * Author:  king -- LiBaoJia
 * Date:    10/28/21 2:28 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * 
 * 
 * 
 ******************************************************************************************************************/


namespace app\common\events\member;


use app\common\events\Event;

class MemberLoginEvent extends Event
{
    private $uid;

    public function __construct($uid)
    {
        $this->uid = $uid;
    }

    public function getUid()
    {
        return $this->uid;
    }
}
