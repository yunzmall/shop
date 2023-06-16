<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVipOrderGoodsPriceToYzOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order')) {
            Schema::table('yz_order', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_order', 'vip_order_goods_price')) {
                    $table->decimal('vip_order_goods_price',14,2)->nullable()->comment('会员价金额');
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
        Schema::table('yz_order', function (Blueprint $table) {
            $table->dropColumn('vip_order_goods_price');
        });
    }
}
