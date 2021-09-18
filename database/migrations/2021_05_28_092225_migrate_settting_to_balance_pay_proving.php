<?php

use app\common\facades\Setting;
use app\common\models\UniAccount;
use Illuminate\Database\Migrations\Migration;

class MigrateSetttingToBalancePayProving extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $uniAccount = UniAccount::getEnable() ?: [];
        foreach ($uniAccount as $u) {

            Setting::$uniqueAccountId = \YunShop::app()->uniacid = $u->uniacid;

            $payProving = Setting::get('shop.pay.balance_pay_proving');

            if ($payProving == 1) {
                Setting::set("pay_password.pay_state", '1');
                Setting::set("pay_password.balance", ['pay']);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
