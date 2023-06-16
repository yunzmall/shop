<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzMemberCouponTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_member_coupon')) {
            Schema::create('yz_member_coupon', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable()->default(0)->index('idx_uniacid');
                $table->string('uid')->nullable()->default('')->comment('会员id');
                $table->integer('coupon_id')->nullable()->default(0)->index('idx_couponid')->comment('优惠券id');
                $table->boolean('get_type')->nullable()->default(0)->index('idx_gettype')->comment('获取优惠券的方式');
                $table->integer('used')->nullable()->default(0)->comment('是否已经使用');
                $table->integer('use_time')->nullable()->default(0)->comment('使用优惠券的时间');
                $table->integer('get_time')->nullable()->default(0)->comment('获取优惠券的时间');
                $table->integer('send_uid')->nullable()->default(0)->comment('手动发放优惠券的操作人员的 uid');
                $table->string('order_sn')->nullable()->default('')->comment('使用优惠券的订单号');
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_member_coupon comment '优惠券--会员优惠券'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_member_coupon');
    }

}
