<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUseNumToYzGoodsCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_goods_coupon')) {
            Schema::table('yz_goods_coupon', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_goods_coupon','use_num')) {
                    $table->integer('use_num')->nullable()->comment('可使用数量');
                }
                if (!Schema::hasColumn('yz_goods_coupon','is_use_num')) {
                    $table->tinyInteger('is_use_num')->nullable()->default(0)->comment('可使用数量开关');
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
