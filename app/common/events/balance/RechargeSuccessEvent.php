<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/7/14 10:36 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/

namespace app\common\events\balance;

use app\common\events\Event;
use app\common\models\finance\BalanceRecharge;

class RechargeSuccessEvent extends Event
{
    protected $rechargeModel;

    public function __construct($model)
    {
        $this->rechargeModel = $model;
    }

    /**
     * @return BalanceRecharge
     */
    public function getRechargeModel()
    {
        return $this->rechargeModel;
    }
}
