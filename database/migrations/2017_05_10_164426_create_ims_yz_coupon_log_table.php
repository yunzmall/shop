<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzCouponLogTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_coupon_log')) {
            Schema::create('yz_coupon_log', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('uniacid')->nullable()->default(0)->index('idx_uniacid');
                $table->string('logno')->nullable()->default('')->comment('日志详情');
                $table->string('member_id')->nullable()->default('')->comment('会员id');
                $table->integer('couponid')->nullable()->default(0)->index('idx_couponid')->comment('优惠券id');
                $table->boolean('paystatus')->nullable()->default(0)->index('idx_paystatus')->comment('支付状态');
                $table->boolean('creditstatus')->nullable()->default(0);
                $table->boolean('paytype')->nullable()->default(0)->comment('支付方式');
                $table->boolean('getfrom')->nullable()->default(0)->index('idx_getfrom')->comment('获取途径');
                $table->integer('status')->nullable()->default(0)->index('idx_status')->comment('状态');
                $table->integer('createtime')->nullable()->default(0)->index('idx_createtime')->comment(' 创建时间');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_coupon_log comment '优惠券--领取发放记录'");//表注释
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_coupon_log');
    }

}
