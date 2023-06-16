<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/3/14
 * Time: 16:32
 */

namespace app\common\events\member;

use app\common\events\Event;

class MemberIntegralChangeEvent extends Event
{
    protected $member;
    protected $source;
    protected $recordData;

    public function __construct($memberLove,$source, $recordData)
    {
        $this->member = $memberLove;

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

    public function getSource()
    {
        return $this->source;
    }
}