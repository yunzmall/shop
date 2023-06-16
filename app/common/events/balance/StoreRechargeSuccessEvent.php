<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/10/19
 * Time: 5:54 下午
 */

namespace app\common\events\balance;

use app\common\events\Event;

class StoreRechargeSuccessEvent extends Event
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
