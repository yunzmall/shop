<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPayRewardBalanceToImsYzGoodsSaleShop extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_sale')) {
            Schema::table('yz_goods_sale', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_sale', 'pay_reward_balance')) {
                    $table->string('pay_reward_balance')->default('')->nullable()->comment('支付奖励值');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('yz_goods_sale', function (Blueprint $table) {
            $table->dropColumn('pay_reward_balance');
        });
    }
}
