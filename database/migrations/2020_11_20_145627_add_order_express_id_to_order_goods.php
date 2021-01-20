<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderExpressIdToOrderGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_order_goods')) {
            Schema::table('yz_order_goods', function (Blueprint $table) {
                //
                if (!Schema::hasColumn('yz_order_goods','order_express_id')) {
                    $table->integer('order_express_id')->nullable()->comment('order_express表id  商品属于哪个包裹');
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
        Schema::table('yz_order_goods', function (Blueprint $table) {
            $table->dropColumn('order_express_id');
        });
    }
}
