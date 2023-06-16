<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBuyMultipleToYzGoodsPrivilegeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_privilege')) {
            Schema::table('yz_goods_privilege', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_privilege', 'buy_multiple')) {
                    $table->integer('buy_multiple')->nullable()->comment('会员购买倍数');
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

    }
}
