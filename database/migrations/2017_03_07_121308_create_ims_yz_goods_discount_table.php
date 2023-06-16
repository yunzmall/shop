<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzGoodsDiscountTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_goods_discount')) {
            Schema::create('yz_goods_discount', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('goods_id')->index('idx_goodid')->comment('商品ID');
                $table->boolean('level_discount_type')->comment('折扣类型1为会员等级');
                $table->boolean('discount_method')->comment('折扣方式，1折扣，2固定金额，3成本比例');
                $table->integer('level_id')->comment('会员等级ID');
                $table->decimal('discount_value', 3)->comment('折扣数值');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_goods_discount comment '商品折扣方式表'");//表注释
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_goods_discount');
	}

}
