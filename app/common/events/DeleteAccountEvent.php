<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022-09-20
 * Time: 17:14
 */

namespace app\common\events;


class DeleteAccountEvent extends Event
{
    protected $uniacid;

    public function __construct($uniacid)
    {
        $this->uniacid = $uniacid;
    }

    public function getUniacid()
    {
        return $this->uniacid;
    }
}