<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalStockToYzGoodsLimitbuy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('yz_goods_limitbuy')) {
            Schema::table('yz_goods_limitbuy', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_limitbuy', 'original_stock')) {
                    $table->integer('original_stock')->default(0)->nullable()->comment('商品原始库存');
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
