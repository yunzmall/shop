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

class MemberBalanceChangeEvent extends Event
{
    protected $member;
    protected $new_value;
    protected $change_value;
    protected $source;

    protected $recordData;

    public function __construct($member,$new_value,$change_value,$source, $recordData)
    {
        $this->member = $member;
        $this->new_value = $new_value;
        $this->change_value = $change_value;
        $this->source = $source;

        $this->recordData = $recordData;
    }

    public function getRecordData()
    {
        return $this->recordData;
    }

    public function getMember()
    {
        return $this->member;
    }

    public function getNewValue()
    {
        return $this->new_value;
    }

    public function getChangeValue()
    {
        return $this->change_value;
    }

    public function getSource()
    {
        return $this->source;
    }
}