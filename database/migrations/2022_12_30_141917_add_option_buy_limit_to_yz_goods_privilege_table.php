<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptionBuyLimitToYzGoodsPrivilegeTable extends Migration
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
                if (!Schema::hasColumn('yz_goods_privilege', 'option_buy_limit')) {
                    $table->text('option_buy_limit')->nullable()->comment('规格限购控制');
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
        Schema::table('yz_goods_privilege', function (Blueprint $table) {
            //
        });
    }
}
