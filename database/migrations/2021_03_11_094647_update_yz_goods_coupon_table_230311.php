<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateYzGoodsCouponTable230311 extends Migration
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
                if (!Schema::hasColumn('yz_goods_coupon', 'no_use')) {
                    $table->tinyInteger('no_use')->default(0)->comment('禁止使用优惠券,1开启（不可使用），0关闭');
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
        Schema::table('yz_goods_coupon', function (Blueprint $table) {
            $table->dropColumn('no_use');
        });
    }
}
