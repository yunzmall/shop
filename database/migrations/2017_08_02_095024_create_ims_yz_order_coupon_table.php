<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderCouponTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!\Schema::hasTable('yz_order_coupon')) {

            Schema::create('yz_order_coupon', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uid')->default(0);
                $table->integer('order_id')->comment('订单ID');
                $table->integer('coupon_id')->comment('优惠券ID');
                $table->integer('member_coupon_id')->default(0)->comment('会员优惠券ID');
                $table->string('name', 100)->default('')->comment('优惠券名称');
                $table->decimal('amount', 10)->default(0.00)->comment('优惠金额');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_coupon` comment'订单--优惠券使用记录'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (\Schema::hasTable('yz_order_coupon')) {

            Schema::drop('yz_order_coupon');
        }
	}

}
