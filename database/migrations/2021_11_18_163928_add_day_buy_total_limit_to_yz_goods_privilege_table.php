<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDayBuyTotalLimitToYzGoodsPrivilegeTable extends Migration
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
                if (!Schema::hasColumn('yz_goods_privilege', 'day_buy_total_limit')) {
                    $table->integer('day_buy_total_limit')->default(0)->comment('商品每日限购总量');
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
