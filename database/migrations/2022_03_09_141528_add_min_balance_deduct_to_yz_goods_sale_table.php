<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinBalanceDeductToYzGoodsSaleTable extends Migration
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
                if (!Schema::hasColumn('yz_goods_sale','min_balance_deduct')) {
                    $table->string('min_balance_deduct',10)->nullable()->comment('余额最低抵扣');
                }

                if (Schema::hasColumn('yz_goods_sale','max_balance_deduct')) {
                    $table->string('max_balance_deduct',10)->nullable()->change()->comment('余额最低抵扣');
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
            //
        });
    }
}
