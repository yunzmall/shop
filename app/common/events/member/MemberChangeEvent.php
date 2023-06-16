<?php
/******************************************************************************************************************
 * Author:  king -- LiBaoJia
 * Date:    10/26/21 3:46 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * 
 * 
 * 
 ******************************************************************************************************************/


namespace app\common\events\member;


use app\common\events\Event;

class MemberChangeEvent extends Event
{
    private $memberModel;

    public function __construct($memberModel)
    {
        $this->memberModel = $memberModel;
    }

    public function getMemberModel()
    {
        return $this->memberModel;
    }
}
