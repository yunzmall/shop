<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2023/2/21
 * Time: 17:08
 */

namespace app\frontend\modules\order\listeners;

use app\common\models\PayCallbackException;
use app\common\models\UniAccount;

class PayExceptionRefundCron
{

    public function handle()
    {
        $uniAccount = UniAccount::getEnable();
        foreach ($uniAccount as $u) {
            \YunShop::app()->uniacid = $u->uniacid;
            \Setting::$uniqueAccountId = $u->uniacid;

            $this->handleTask();
        }
    }

    public function handleTask()
    {

        //$current_time = time();

        $payExceptions = PayCallbackException::uniacid()
            ->where('error_code', PayCallbackException::ORDER_CLOSE)
            ->where('status', PayCallbackException::INITIAL)
            ->get();

        if ($payExceptions->isNotEmpty()) {
            foreach ($payExceptions as $payException) {
                $payException->refund();
            }
        }
    }
}