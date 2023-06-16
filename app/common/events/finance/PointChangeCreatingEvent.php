<?php
/******************************************************************************************************************
 * Author:  king -- LiBaoJia
 * Date:    11/10/21 9:50 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * 
 * 
 * 
 ******************************************************************************************************************/


namespace app\common\events\finance;


use app\common\events\Event;

class PointChangeCreatingEvent extends Event
{
    /**
     * @var array
     */
    public $changeData;

    public $is_change;

    public function __construct($changeData)
    {
        $this->changeData = $changeData;
        $this->is_change = 1;
    }

    public function changeData()
    {
        return $this->changeData;
    }
}
