<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderGoodsDiscountTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!\Schema::hasTable('yz_order_goods_discount')) {

            Schema::create('yz_order_goods_discount', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uid')->default(0);
                $table->integer('order_goods_id');
                $table->string('discount_code', 50)->default('')->comment('折扣代码');
                $table->string('name', 100)->default('')->comment('名称');
                $table->decimal('amount', 10)->default(0.00)->comment('金额');
                $table->boolean('is_indirect')->default(0)->comment('间接计算出');
                $table->integer('updated_at')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_goods_discount` comment'订单--商品优惠记录'");//表注释

        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (\Schema::hasTable('yz_order_goods_discount')) {
            Schema::drop('yz_order_goods_discount');
        }
	}

}
