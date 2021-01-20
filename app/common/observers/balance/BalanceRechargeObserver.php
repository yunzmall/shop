<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    2020/7/14 10:09 AM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/


namespace app\common\observers\balance;


use app\common\events\balance\RechargeSuccessEvent;
use app\common\models\finance\BalanceRecharge;
use app\common\observers\BaseObserver;
use app\common\services\credit\ConstService;
use Illuminate\Database\Eloquent\Model;

class BalanceRechargeObserver extends BaseObserver
{
    public function updated(Model $model)
    {
        /**
         * @var BalanceRecharge $model
         */
        if ($model->status == ConstService::STATUS_SUCCESS) {
            event((new RechargeSuccessEvent($model)));
        }
    }
}
