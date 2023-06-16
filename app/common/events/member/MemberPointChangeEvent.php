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

class MemberPointChangeEvent extends Event
{
    protected $member;
    protected $point_data;
    protected $status;

    public function __construct($member,$point_data,$status)
    {
        $this->member = $member;
        $this->point_data = $point_data;
        $this->status = $status;
    }

    public function getMember()
    {
        return $this->member;
    }

    public function getPointData()
    {
        return $this->point_data;
    }

    public function getStatus()
    {
        return $this->status;
    }
}