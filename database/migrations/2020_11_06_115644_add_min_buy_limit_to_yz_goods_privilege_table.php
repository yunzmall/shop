<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinBuyLimitToYzGoodsPrivilegeTable extends Migration
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

                if (!Schema::hasColumn('yz_goods_privilege', 'min_buy_limit')) {
                    $table->integer('min_buy_limit')->nullable()->default(0)->comment('会员起购数量');
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
        //
    }
}
