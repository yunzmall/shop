<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBalanceDeductToYzGoodsSaleTable extends Migration
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
                if (!Schema::hasColumn('yz_goods_sale','balance_deduct')) {
                    $table->integer('balance_deduct')->default(1)->comment('余额抵扣开关');
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
