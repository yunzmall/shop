<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntegralExchangeColumnToYzCoupon extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_coupon')) {
            Schema::table('yz_coupon', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_coupon', 'is_integral_exchange_coupon')) {
                    $table->tinyInteger('is_integral_exchange_coupon')->default(0)->comment('消费积分兑换优惠券：1-开启，0-关闭');
                }
                if (!Schema::hasColumn('yz_coupon', 'exchange_coupon_integral')) {
                    $table->decimal('exchange_coupon_integral',10,2)->nullable()->comment('兑换优惠券需要消费积分');
                }
                if (!Schema::hasColumn('yz_coupon', 'member_tags_names')) {
                    $table->text('member_tags_names',65535)->after('storenames')->nullable()->comment('会员标签名称');
                }
                if (!Schema::hasColumn('yz_coupon', 'member_tags_ids')) {
                    $table->text('member_tags_ids',65535)->after('storenames')->nullable()->comment('会员标签id组');
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
        Schema::table('yz_coupon', function (Blueprint $table) {
            $table->dropColumn('is_integral_exchange_coupon');
            $table->dropColumn('exchange_coupon_integral');
            $table->dropColumn('member_tags_names');
            $table->dropColumn('member_tags_ids');
        });
    }
}
