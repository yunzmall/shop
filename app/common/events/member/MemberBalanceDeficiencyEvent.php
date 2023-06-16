<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/4/27
 * Time: 14:09
 */

namespace app\common\events\member;


use app\common\events\Event;

class MemberBalanceDeficiencyEvent extends Event
{
    protected $member;
    protected $new_value;
    protected $balanceSet;

    public function __construct($member,$balanceSet,$new_value)
    {
        $this->member = $member;
        $this->new_value = $new_value;
        $this->balanceSet = $balanceSet;
    }

    public function getMember()
    {
        return $this->member;
    }

    public function getNewValue()
    {
        return $this->new_value;
    }

    public function getBalanceSet()
    {
        return $this->balanceSet;
    }
}