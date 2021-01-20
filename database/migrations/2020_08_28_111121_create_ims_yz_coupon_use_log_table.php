<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzCouponUseLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_coupon_use_log')) {
            Schema::create('yz_coupon_use_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号');
                $table->integer('member_id')->comment('会员ID');
                $table->string('detail')->nullable()->comment('详情');
                $table->integer('coupon_id')->index('idx_couponid')->comment('优惠券ID');
                $table->integer('member_coupon_id')->index('idx_membercouponid')->comment('会员优惠券ID');
                $table->integer('type')->comment('类型');
                $table->string('remark')->nullable()->comment('备注');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
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
